#!/bin/sh
httpd &

#--no-defaults 
mysqld --user=root \
    --max-connections=1000 \
    --max-allowed-packet=4M \
    --connect-timeout=5 \
    --wait-timeout=10 \
    --tcp-keepalive-time=10 \
    --default-time-zone=+9:00