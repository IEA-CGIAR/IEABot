<?php
header("Content-type: text/plain");

require_once("db_data.php");
require_once("Telegram.php");
require_once("php-curl-class/src/Curl/Curl.php");

$t = new Telegram();
$pdo = new PDO("mysql:host=" . $db->host . ";dbname=" . $db->database, $db->username, $db->password);
$sql_bkp = "select `uid`, `name`, `pass` from ieacgiar_zuluiea.users__bkp";
$sql_prod = "select `uid`, `name`, `pass` from ieacgiar_zuluiea.users";
$query_bkp = $pdo->query($sql_bkp);
$query_prod = $pdo->query($sql_prod);
$restore = false;
$cmp = new stdClass();
while($row_bkp = $query_bkp->fetch(PDO::FETCH_ASSOC)) {
    $cmp->bkp[] = $row_bkp;
}
while($row_prod = $query_prod->fetch(PDO::FETCH_ASSOC)) {
    $cmp->prod[] = $row_prod;
}
// print_r($cmp);

if(count($cmp->bkp) !== count($cmp->prod)) {
    /**
     * RESTORE
     */
    $restore = true;
} else {
    foreach($cmp->bkp as $k => $v) {
        $result[] = array_diff_assoc($cmp->prod[$k], $cmp->bkp[$k]);
    }
    if(isset($result) && count($result) > 0) {
        /**
         * RESTORE
         */
        $restore = true;
    }
}
if($restore) {
    if(!isset($result) || count($result) == 0) {
        if(trim($cmp->prod[0]["name"]) == "" || trim($cmp->prod[0]["pass"]) == "") {
            $pdo->query("delete from ieacgiar_zuluiea.users where uid = " . $cmp->prod[0]["uid"] . ";");
            $msg = "*WARNING*\nFound an anonymous unexisting user (empty row) in the users database, the row was deleted";
            // print $msg;
            $t->sendMessage(-8815985, $msg);
        }
    } else {
        // Restore user credentials
        foreach($result as $k => $v) {
            foreach($v as $kk => $vv) {
                switch($kk) {
                    case "name":
                        if(strlen(trim($cmp->prod[$k][$kk])) > 0) {
                            $msg = '*DANGER*' . "\n" . 'Detected an hacking attack: the current *username* "' . str_replace("_", "\_", $cmp->prod[$k][$kk]) . '" is different from the previous one ("' . str_replace("_", "\_", $cmp->bkp[$k][$kk]) . '").' ."\n" . 'The default username was restored';
                        } else {
                            $msg = '*DANGER*' . "\n" . 'Detected an hacking attack: the current *username* for the user "' . str_replace("_", "\_", $cmp->bkp[$k][$kk]) . '" was deleted!' ."\n" . 'The default username was restored';
                        }
                        break;
                    case "pass":
                        $msg = '*DANGER*' . "\n" . 'Detected an hacking attack: the password of the user "' . str_replace("_", "\_", $cmp->bkp[$k]["name"]) . '" was changed!' . "\n" . 'The previous one was restored';
                }
                $pdo->query("update ieacgiar_zuluiea.users set `" . $kk . "`='" . $cmp->bkp[$k][$kk] . "' where `uid` = " . $cmp->bkp[$k]["uid"] . ";");
                // print $msg;
                $t->sendMessage(-8815985, $msg);
            }
        }
    }
}
// print_r($result);
?>
