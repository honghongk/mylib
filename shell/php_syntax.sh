#!/bin/sh

# 경로 받는 추가처리 필요

# 단순 문법확인

find | grep php$ | xargs -i php -l {}
