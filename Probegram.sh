#!/bin/bash

# MONITORED RESOURCE
# The IP Address that shall be monitored
target_ip=192.168.36.188 #192.156.137.238
# Default HTTP port
port=80
# Timeout in seconds after than the resurce will be considered offline
timeout=3

# TELEGRAM GROUP CHAT
# The chat_id
chat_id=8815985
# Bot name
botname="IEAbot"
# The notification message
msg="The site is offline!"


/bin/nc -ndi 1 -w $timeout $target_ip $port &> /dev/null || (echo "msg chat#$chat_id $msg"; echo "safe_quit") | /usr/bin/telegram-cli -bDp $botname -W

