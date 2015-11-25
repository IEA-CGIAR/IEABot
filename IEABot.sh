#!/bin/bash

# MONITORED RESOURCE
# The IP Address that shall be monitored
target_ip=192.168.1.1
# Default HTTP port, use 443 for HTTPS
port=80
# Timeout in seconds after than the resurce will be considered offline
timeout=30

# TELEGRAM GROUP CHAT
# The chat_id
chat_id="Your chat ID"
# Bot name
botname="IEABot"
# The notification message
msg="The site is offline!"


/bin/nc -ndi 1 -w $timeout $target_ip $port &> /dev/null || (echo "msg chat#$chat_id $msg"; echo "safe_quit") | /usr/bin/telegram-cli -bDp $botname -W

