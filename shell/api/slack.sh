#!/bin/sh


slack_send()
{
    local url token channel msg;
    url='https://slack.com/api/chat.postMessage'
    token='xoxb-token';
    channel=$1;
    msg=$2;

    curl -X POST \
    -H "Authorization: Bearer ${token}" \
    -d "channel=${channel}&text=${msg}" \
    $url > /dev/null 2&>1
}
