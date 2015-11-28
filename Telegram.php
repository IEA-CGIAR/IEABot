<?php

class Telegram {
    /**
     * The Telegram API url
     *
     * @var string
     */
    public static $apiUrl = "https://api.telegram.org/";
    public $sent_message;

    /**
     * Construct
     */
    function __construct() {
        $this->api = self::$apiUrl . "bot121557248:AAEQUfb1Be2-Zlyxk-VJ7Jbskp3bIHXRVMY/";
        $this->sent_message = new stdClass();
        return $this->api;
    }

    /**
     * Import the class Curl
     *
     * @param  string   $url                The url to parse
     * @param  array    $request            The requests as array of key => value
     * @param  string   $method             Specify the method to send (GET or POST). Default "get"
     * @return string                       The json of the
     */
    public function curl($url, $request = array(), $method = "get") {
        try {
            $curl = new \Curl\Curl();
            if($method == "get") {
                $curl->get($url, $request);
            } else {
                $curl->post($url, $request);
            }
            // print_r($request);
            // print "\n\n";
            // if(gettype($result) == "object" || gettype($result) == "array") {
            //     return json_encode((array)$result);
            //         // return "ok array";//$result;
            // } else {
                return $curl->response;
            // }
            $curl->close();
        } catch(Exception $e) {
            error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }

    }

    /**
     * Convert a Unicode codepoint to a UTF-8 string.
     * Very useful for emoji.
     * @see http://apps.timwhitlock.info/emoji/tables/unicode
     *
     * @param  string   $i                  The Unicode character to convert
     * @return string                       The converted UTF-8 string
     */
    public function emoji($i) {
        return iconv("UCS-4LE", "UTF-8", pack("V", $i));
    }

    /**
     * Load additional interface options
     *
     * @param  object   $replyMarkup        The object with additional interface options. @see https://core.telegram.org/bots/api#replykeyboardmarkup
     * @return string                       A string with json object of passed markup
     */
    private function parseReplyMarkup($replyMarkup) {
        // print gettype($replyMarkup);
        // exit();
        switch(gettype($replyMarkup)) {
            case "boolean":
                if($replyMarkup === true) {
                    $params = json_encode(array("hide_keyboard" => true));
                } else {
                }
                break;
            case "array":
            case "object":
                $params = json_encode($replyMarkup);
                break;
            case "string":
                if($replyMarkup == "forward") {
                    $params = json_encode(array("force_reply" => true));
                }
            case "NULL":
            default:
                $params = json_encode($replyMarkup);
                break;
        }
        return $params;
    }

    /**
     * Get some info about the BOT itself
     *
     * @return string                       Return a json with BOT data
     */
    public function info() {
        $cmd = "getMe";
        return json_encode($this->curl($this->api . $cmd, array("url" => "")));
    }

    /**
     * Send an action text
     *
     * @param  int      $receiver           The id of the receiver of the message
     * @param  string   $action             The text of the action
     * @return string                       The response json data of sent message
     */
    private function sendChatAction($receiver, $action) {
        try {
            $cmd = "sendChatAction";
            $params = array(
                "chat_id"               => $receiver,
                "action"                => $action,
            );
            return json_encode($this->curl($this->api . $cmd, $params, "get"));
        } catch(Exception $e) {
            error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }

    /**
     * Get chat updates
     *
     * @param  int      $offset             Identifier of the first update to be returned
     * @param  int      $limit              Limits the number of updates to be retrieved. Values between 1—100 are accepted. Defaults to 100
     * @param  int      $timeout            Timeout in seconds for long polling
     * @return string                       The updated chat in json format
     */
    public function getUpdates($offset = null, $limit = null, $timeout = null) {
        try {
            $cmd = "getUpdates";
            $params = array(
                "offset"                    => $offset,
                "limit"                     => $limit,
                "timeout"                   => $timeout
            );
            return json_encode($this->curl($this->api . $cmd, $params));
        } catch(Exception $e) {
                       error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }

    /**
     * Add a Webhook
     * Useful when Webhooks versions are in conflict
     *
     * @return string                       The result of the Curl()
     */
    public function addWebhook() {
        try {
            $cmd = "setWebhook";
            // print $this->api . $cmd . http_build_query(array(
            //     "url" => "https://listener.probegram.pw/index.php",
            //     "certificate" => "/etc/apache2/ssl/probegram.crt"));
            // exit();
            return $this->curl($this->api . $cmd, array(
                "url" => "https://listener.probegram.pw/index.php",
                "certificate" => "/etc/apache2/ssl/probegram.crt"
            ), "post");
        } catch(Exception $e) {
                       error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }

    /**
     * Delete a Webhook
     * Useful when Webhooks versions are in conflict
     *
     * @return string                       The result of the Curl()
     */
    public function deleteWebhook() {
        try {
            $cmd = "setWebhook";
            return $this->curl($this->api . $cmd, array("url" => ""));
        } catch(Exception $e) {
                       error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }

    /**
     * Update a Webhook
     * Useful when Webhooks versions are in conflict
     *
     * @return string                       The result of the Curl()
     */
    public function updateWebhook() {
        print_r($this->deleteWebhook());
        print_r($this->addWebhook());
    }


    /* -----------------------------------------------------------------
    SEND TEXT MESSAGES
    ----------------------------------------------------------------- */

    /**
     * Send a message
     *
     * @param  int      $receiver           The id of the receiver of the message
     * @param  string   $text               The text to send
     * @param  bool     $md                 Allow Markdown? Default true
     * @param  bool     $linkPreview        Disable page preview for links? Default false
     * @param  int      $replyTo            The message id to reply
     * @param  string   $replyMarkup        A json object with interface params to the markup
     * @return string                       The response json data of sent message
     */
    public function sendMessage($receiver, $text, $allowMd = true, $linkPreview = false, $replyTo = null, $replyMarkup = null) {
        try {
            $cmd = "sendMessage";
            $md = (!$allowMd) ? null : "Markdown";
            $params = array(
                "chat_id"                   => $receiver,
                "text"                      => $text,
                "parse_mode"                => $md,
                "disable_web_page_preview"  => $linkPreview,
                "reply_to_message_id"       => (int)$replyTo
            );
            $params["reply_markup"] = $this->parseReplyMarkup($replyMarkup);

            $this->sendChatAction($receiver, "typing");
            $message = $this->curl($this->api . $cmd, $params);
            $this->sent_message = $message->result;

            // $this->db->log_chat(json_encode($message), "sent");
           error_log(print_r($this->sent_message, 1), 3, "logs/Telegram/sent_messages.log");
            return json_encode($message);
        } catch(Exception $e) {
            error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }

    /**
     * Forward a message
     *
     * @param  int      $receiver           The id of the receiver of the message
     * @param  int      $sourceChat         The message chat id
     * @param  int      $messageID          The message id to reply
     * @return string                       The response json data of sent message
     */
    public function forwardMessage($receiver, $sourceChat, $messageID) {
        try {
            $cmd = "forwardMessage";
            $this->sendChatAction($receiver, "typing");
            return json_encode($this->curl(
                $this->api . $cmd,
                array(
                    "chat_id"       => $receiver,
                    "from_chat_id"  => $sourceChat,
                    "message_id"    => (int)$messageID
                )
            ));
        } catch(Exception $e) {
            error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }


    /* -----------------------------------------------------------------
    SEND A LOCATION
    ----------------------------------------------------------------- */

    /**
     * Send a a point on the map
     *
     * @param  int              $receiver           The id of the receiver of the message
     * @param  array|string     $place              An array (lat, lon) or a string of the place name
     * @param  int              $replyToMessageID   If the message is a reply, the ID of the original message
     * @param  string           $replyMarkup        A json object with interface params to the markup
     * @return string                               The response json data of sent message
     */
    public function sendLocation($receiver, $place, $replyToMessageID = null, $replyMarkup = null) {
        try {
            $cmd = "sendLocation";
            if(is_array($place)) {
                $latitude = $place[0];
                $longitude = $place[1];
            } else {
                $geosearch = $this->curl("http://nominatim.openstreetmap.org/search", array(
                    "format" => "json",
                    "limit" => 1,
                    "q" => $place
                ));
                print_r($geosearch);
                exit();
                $latitude = $geosearch->lat;
                $longitude = $geosearch->lon;
            }
            $params = array(
                "chat_id"               => $receiver,
                "latitude"              => $latitude,
                "longitude"             => $longitude,
                "reply_to_message_id"   => $replyToMessageID,
            );
            $params["reply_markup"] = $this->parseReplyMarkup($replyMarkup);
            $this->sendChatAction($receiver, "find_location");
            return json_encode($this->curl($this->api . $cmd, $params));
        } catch(Exception $e) {
            error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }


    /* -----------------------------------------------------------------
    SEND FILES
    ----------------------------------------------------------------- */

    /**
     * Send a picture
     *
     * @param  int      $receiver           The id of the receiver of the message
     * @param  string   $document           The file name (on server) to send
     * @param  int      $replyToMessageID   If the message is a reply, the ID of the original message
     * @param  string   $replyMarkup        A json object with interface params to the markup
     * @return string                       The response json data of sent message
     */
    public function sendDocument($receiver, $document, $replyToMessageID = null, $replyMarkup = null) {
        try {
            $cmd = "sendDocument";
            $params = array(
                "chat_id"               => $receiver,
                "document"              => "@" . $document,
                "caption"               => $caption,
                "reply_to_message_id"   => $replyToMessageID,
            );
            $params["reply_markup"] = $this->parseReplyMarkup($replyMarkup);
            // print_r($params);
            $this->sendChatAction($receiver, "upload_document");
            return json_encode($this->curl($this->api . $cmd, $params, "post"));
        } catch(Exception $e) {
            error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }

    /**
     * Send a picture
     *
     * @param  int      $receiver           The id of the receiver of the message
     * @param  string   $photo              The file name (on server) to send
     * @param  string   $caption            The caption text
     * @param  int      $replyToMessageID   If the message is a reply, the ID of the original message
     * @param  string   $replyMarkup        A json object with interface params to the markup
     * @return string                       The response json data of sent message
     */
    public function sendPhoto($receiver, $photo, $caption = null, $replyToMessageID = null, $replyMarkup = null) {
        try {
            $cmd = "sendPhoto";
            $params = array(
                "chat_id"               => $receiver,
                "photo"                 => "@" . $photo,
                "caption"               => $caption,
                "reply_to_message_id"   => $replyToMessageID,
            );
            $params["reply_markup"] = $this->parseReplyMarkup($replyMarkup);
            // print_r($params);
            $this->sendChatAction($receiver, "upload_photo");
            return json_encode($this->curl($this->api . $cmd, $params, "post"));
        } catch(Exception $e) {
            error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }

    /**
     * Send a sticker
     *
     * @param  int      $receiver           The id of the receiver of the message
     * @param  string   $sticker            The file name (on server) to send
     * @param  int      $replyToMessageID   If the message is a reply, the ID of the original message
     * @param  string   $replyMarkup        A json object with interface params to the markup
     * @return string                       The response json data of sent message
     */
    public function sendSticker($receiver, $sticker, $replyToMessageID = null, $replyMarkup = null) {
        try {
            $cmd = "sendSticker";
            $params = array(
                "chat_id"               => $receiver,
                "sticker"               => "@" . $sticker,
                "reply_to_message_id"   => $replyToMessageID,
            );
            $params["reply_markup"] = $this->parseReplyMarkup($replyMarkup);
            // print_r($params);
            $this->sendChatAction($receiver, "upload_photo");
            return json_encode($this->curl($this->api . $cmd, $params, "post"));
        } catch(Exception $e) {
            error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }

    /**
     * Send an audio file
     *
     * @param  int      $receiver           The id of the receiver of the message
     * @param  string   $voice              The file name (on server) to send
     * @param  int      $duration           Duration of sent audio in seconds
     * @param  int      $replyToMessageID   If the message is a reply, the ID of the original message
     * @param  string   $replyMarkup        A json object with interface params to the markup
     * @return string                       The response json data of sent message
     */
    public function sendVoice($receiver, $voice, $duration = null, $replyToMessageID = null, $replyMarkup = null) {
        try {
            $cmd = "sendVoice";
            $params = array(
                "chat_id"               => $receiver,
                "voice"                 => "@" . $voice,
                "duration"              => $duration,
                "reply_to_message_id"   => $replyToMessageID,
            );
            $params["reply_markup"] = $this->parseReplyMarkup($replyMarkup);
            // print_r($params);
            $this->sendChatAction($receiver, "upload_audio");
            return json_encode($this->curl($this->api . $cmd, $params, "post"));
        } catch(Exception $e) {
            error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }

    /**
     * Send a video file
     *
     * @param  int      $receiver           The id of the receiver of the message
     * @param  string   $video              The file name (on server) to send
     * @param  string   $caption            The caption text
     * @param  int      $duration           Duration of sent audio in seconds
     * @param  int      $replyToMessageID   If the message is a reply, the ID of the original message
     * @param  string   $replyMarkup        A json object with interface params to the markup
     * @return string                       The response json data of sent message
     */
    public function sendVideo($receiver, $video, $caption = null, $duration = null, $replyToMessageID = null, $replyMarkup = null) {
        try {
            $cmd = "sendVideo";
            $params = array(
                "chat_id"               => $receiver,
                "video"                 => "@" . $video,
                "duration"              => $duration,
                "caption"               => $caption,
                "reply_to_message_id"   => $replyToMessageID,
            );
            $params["reply_markup"] = $this->parseReplyMarkup($replyMarkup);
            // print_r($params);
            $this->sendChatAction($receiver, "upload_video");
            return json_encode($this->curl($this->api . $cmd, $params, "post"));
        } catch(Exception $e) {
            error_log(print_r($e->getMessage(), 1), 3, "logs/Telegram/errors.log");
        }
    }

    function __destruct() {
        // print "OK\n";
        // print_r($this->sent_message);
    }
}
?>
