#!/bin/sh

source $pwd/lib/array.sh
source $pwd/lib/output.sh


redis_cli=`which redis-cli`;
if [[ ! -f $redis_cli ]];then
	error 'redis-cli 없음'
fi

redis_cli=${redis_cli}' -h '$host' -a '$passwd' --no-auth-warning ';
redis_cli_query=${redis_cli}' -h '$host' -a '$passwd' -n '$db' --raw ';

#######################################
# 연결 테스트 성공시 PONG 이면 성공
#######################################
function redis_cli_ping ()
{
	pong=`$redis_cli ping`
	if [[ $pong != 'PONG' ]];then
		alert '연결 에러'$redis_cli
		return 1 ;
	fi

	return 0 ;
}


#######################################
# DB 선택
# cli 옵션에 있기 때문에
# DB 확인 후 변수 덮어쓰기
#######################################
function redis_cli_select()
{
	local res;

	if [ $# -ne 1 ];then
		alert '파라미터 에러 : redis_cli_select db_index';
		return 1;
	fi

	res=(`$redis_cli select $1`);
	if [[ $res != 'OK' ]];then
		alert 'DB선택 에러';
		return 1;
	fi
	redis_cli_query='redis-cli -h '$host' -a '$passwd' -n '$1' --raw ';
	return 0;
}
#######################################
# 미완성
# 계속 돌면서 출력하기 때문에 안됨
#######################################
function redis_cli_stat()
{
	error '미완성';
	echo 스테이터스함수
	echo ${*[@]}
	#`$redis_cli --stat`
	echo 스테이터스함수끝
}
#######################################
# rdb 백업
#######################################
function redis_cli_rdb_dump()
{
	if [ $# -ne 1 ];then
		alert '파라미터 에러 : redis_cli_rdb_dump /path/to/file';
		return 1;
	fi
	if [ ! -d `dirname $(realpath $1)` ];then
		alert '없는 경로';
		return 2;
	fi
	`$redis_cli --rdb $1`
	if [[ $? -ne 0 ]];then
		alert 'rdb_dump 실패';
		return 1 ;
	fi
	return 0 ;
}
#######################################
# 미완성
# 따로 명령어 옵션은 없고
# 모든 키, 값 조회해서 파일로 쓰는 듯함
#######################################
function redis_cli_aof_dump ()
{
	error 'redis_cli_aof_dump 미완성';
	local scan path ;
	if [ $# -ne 1 ];then
		alert '파라미터 에러 : redis_cli_aof_dump /path/to/file';
		return 1;
	fi
	path=`realpath $1 2>/dev/null`;
	if [ $? -ne 0 ];then
		alert '없는 경로';
		return 2;
	fi
	if [ ! -d `dirname $path` ];then
		alert '없는 경로';
		return 2;
	fi
	scan=`redis_cli_scan`
	echo $scan
	return 0 ;
}
#######################################
# 레디스 내 모든 키 개수
#######################################
function redis_cli_dbsize ()
{
	echo `$redis_cli dbsize`
	return 0 ;
}
#######################################
# mysql의 status 비슷함
#######################################
function redis_cli_info ()
{
	local list section ;
	list=('server' 'clients' 'memory' 'persistence' 'stats' 'replication' 'cpu' 'cluster' 'keyspace');
	if [ $# -gt 1 ];then
		alert '파라미터 에러 : redis_cli_info [list]' ;
		alert 섹션은 ${list[@]} 중에서 하나만 선택해야 합니다.;
		return 1;
	fi
	section=`str_lower $1`
	array_in $section ${list[@]};
	if [ $? -ne 0 ]; then
		alert 섹션은 ${list[@]} 중에서 하나만 선택해야 합니다.;
		return 2
	fi
	$redis_cli info $1 | while read line;do
		echo $line;
	done
	return 0 ;
}
#######################################
# key 조회
# 정규식이 쉘이나 php와는 다를 수 있음
#######################################
function redis_cli_scan ()
{
	if [ $# -gt 1 ];then
		alert '파라미터 에러 : redis_cli_scan [key]';
		return 1;
	fi
	if [[ $1 == '' ]];then
		echo `$redis_cli_query --scan --pattern '*'`
	else
		echo `$redis_cli_query --scan --pattern "$1"`
	fi
	return 0 ;
}
#######################################
# key = value string 타입 출력
#######################################
function redis_cli_get ()
{
	if [ $# -ne 1 ];then
		alert '파라미터 에러 : redis_cli_get key';
		return 1;
	fi
	if [[ $1 == '*' ]];then
		alert '사용 불가능한 문자';
		return 2;
	fi
	echo `$redis_cli_query get "${1//[\']/\'}"`
	return 0 ;
}
#######################################
# key = value string 타입 입력
#######################################
function redis_cli_set ()
{
	local timeout;
	if [[ $# -lt 2 || $# -gt 3 ]];then
		alert '파라미터 에러 : redis_cli_set key value --timeout=timeout(sec)';
		return 1;
	fi
	if [[ $3 != '' ]];then
		timeout="EX $3"
	fi
	if [[ `$redis_cli_query set "${1//[\']/\'}" "${2//[\']/\'}" $timeout` != 'OK' ]];then
		alert 레디스 쿼리에러
		return 1;
	fi
	return 0;
}
#######################################
# key = value list 타입 입력
# json에서 ["item1","item2"]와 비슷한 것
# 순서 없는 배열
#######################################
function redis_cli_push ()
{
	local timeout;
	if [[ $# -lt 2 || $# -gt 3 ]];then
		alert '파라미터 에러 : redis_cli_push key value [timeout(sec)]';
		return 1;
	fi
	if [[ $3 != '' ]];then
		timeout="EX $3"
	fi
	if [[ `$redis_cli_query rpush "${1//[\']/\'}" "${2//[\']/\'}" $timeout` != 'OK' ]];then
		alert 레디스 쿼리에러
		return 1;
	fi
	return 0;
}
#######################################
# key = value list 타입 입력
# json에서 ["item1","item2"]와 비슷한 것
# 순서 없는 배열
#######################################
function redis_cli_unshift ()
{
	local timeout;
	if [[ $# -lt 2 || $# -gt 3 ]];then
		alert '파라미터 에러 : redis_cli_unshift key value [timeout(sec)]';
		return 1;
	fi
	if [[ $3 != '' ]];then
		timeout="EX $3"
	fi
	if [[ `$redis_cli_query lpush "${1//[\']/\'}" "${2//[\']/\'}" $timeout` != 'OK' ]];then
		alert 레디스 쿼리에러
		return 1;
	fi
	return 0;
}
#######################################
# key = value list 타입 출력
# 순서 없는 배열에서 하나씩 빼는 것
# 뺀 값은 지워짐
#######################################
function redis_cli_pop ()
{
	local timeout;
	if [[ $# -ne 1 ]];then
		alert '파라미터 에러 : redis_cli_pop key';
		return 1;
	fi
	if [[ `$redis_cli_query rpop "${1//[\']/\'}" $timeout` != 'OK' ]];then
		alert 레디스 쿼리에러
		return 1;
	fi
	return 0;
}
#######################################
# key = value list 타입 출력
# 순서 없는 배열에서 하나씩 빼는 것
# 뺀 값은 지워짐
#######################################
function redis_cli_shift ()
{
	local timeout;
	if [[ $# -ne 1 ]];then
		alert '파라미터 에러 : redis_cli_shift key';
		return 1;
	fi
	if [[ `$redis_cli_query lpop "${1//[\']/\'}" $timeout` != 'OK' ]];then
		alert 레디스 쿼리에러
		return 1;
	fi
	return 0;
}
#######################################
# 레디스의 유닉스 타임스탬프
#######################################
function redis_cli_time()
{
	local res;
	if [[ $# -ne 0 ]];then
		alert 'NOTICE : 파라미터 사용안함 : redis_cli_time';
	fi
	res=(`$redis_cli_query time`)
	echo $res
	return 0 ;
}
#######################################
# 레디스의 오늘 날짜
#######################################
function redis_cli_today()
{
	local time;
	if [[ $# -ne 0 ]];then
		alert 'NOTICE : 파라미터 사용안함 : redis_cli_today';
	fi
	time=`redis_cli_time`;
	echo `date +%Y%m%d --date="@${time}"`
	return 0 ;
}
alias redis_cli_push=redis_cli_rpush;
alias redis_cli_unshift=redis_cli_lpush;
alias redis_cli_shift=redis_cli_lpop;
alias redis_cli_pop=redis_cli_rpop;