#!/bin/sh

######################
# 현재시간을 출력한다
# 초단위까지 공백없이
######################
function time_stamp()
{
	if [[ $# -ne 0 ]];then
		alert 'NOTICE : 파라미터 사용안함 : time_stamp';
	fi
	echo `date +%Y%m%d%H%M%S`
}

######################
# 오늘날짜를 출력한다
# 공백없이
######################
function time_today()
{
	if [[ $# -ne 0 ]];then
		alert 'NOTICE : 파라미터 사용안함 : time_today';
	fi
	echo `date +%Y%m%d`
}

######################
# 현재시간을 unix 포맷으로 출력한다
# 1900-01-01 00:00:00 을 0으로 해서 (확인안해봄)
# 현재시간까지 1초 단위로 합한것
######################
function time_unix()
{
	if [[ $# -ne 0 ]];then
		alert 'NOTICE : 파라미터 사용안함 : time_today';
	fi
	echo `date +%s`
}