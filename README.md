# Probegram
This is a simple bash script that checks an HTTP URL and send you a notify if the resource is not reachable.<br />
Note that you need `telegram-cli` installed on your machine.

====

### Step by step installation

#### #1 Dependancies
First of all you need to install dependancies:
* **Telegram Messenger CLI**<br />For Ubuntu/Debian systems copy and pase in your terminal the line below. For other systems follow [the official guide](https://github.com/vysheng/tg#installation)
```bash
 sudo apt-get install libreadline-dev libconfig-dev libssl-dev lua5.2 liblua5.2-dev libevent-dev libjansson-dev libpython-dev make  
```
* **Netcat**<br />Ubuntu/Debian systems (not really needed because installed by default):
```bash
sudo apt-get install netcat
```
====

#### #2 Configure Telegram Chat and BOT

> **Tip**: I suggest also to install [Telegram Desktop version](https://desktop.telegram.org/), this will be very useful for BOT configuration.

##### Create a BOT
Telegram has an an assistant to create BOTs, called [BotFather](https://telegram.me/botfather).<br />
So let's launch [BotFather](https://telegram.me/botfather) and provide the first commands to create your own BOT.<br />
In BotFather's chat type:
* `/start`
* then `/newbot`
* then type the dysplay name of your BOT
* then type the **username** of your BOT.<br />Must to finish with "BOT" (case insensitive) and must have no spaces.

You're done, the BOT is created.

##### Notify a group of users
If you want to notify only yourself, you can skip this step and use your Telegram display name instead of the numeric id (replace all spaces with "_" underscores).<br />
Otherwise take your phone, create a new chat group and invite your BOT and your participants.

The next thing to do is to retrieve the Chat ID (a loss a lot of time understanding how to do).<br />
To do so, open your terminal and type: `telegram-cli -W`, and in the telegram session type: `chat_info <YOUR CHAT NAME>`.<br />
You can find the chat id in yellow between parentheses.<br />
Pretty simple!

> **Tip**: Remember that in the Telegram CLI session you can use the Tab key for autocompletition

====

#### #3 Configure the script
Open the [Probegram.sh](https://github.com/gubi/Probegram/blob/master/Probegram.sh) with your favourite text editor and change all variables as you like.<br />
Leave untouched line 20.

====

#### #4 Create your cron
In a terminal type:
```bash
crontab -e
```
Then edit with vim or nano and add the following line
```Bash
# Crontab structure
#
#  * * * * * command to be executed
#  - - - - -
#  | | | | |
#  | | | | ----- Day of week (0 - 7) (Sunday=0 or 7)
#  | | | ------- Month (1 - 12)
#  | | --------- Day of month (1 - 31)
#  | ----------- Hour (0 - 23)
#  ------------- Minute (0 - 59)

*/5 * * * * /home/Probegram/Probegram.sh >> /home/Probegram/offline_status.log 2>&1
```
The cron above will run every 5 minutes and save the output to a log file if something goes wrong.



That's it!
