#!/bin/sh

__FILE__=`realpath $0`
__DIR__=`dirname $__FILE__`
__LIB__=${__DIR__}/lib

source ${__LIB__}/array.sh
source ${__LIB__}/time.sh
source ${__DIR__}/api/slack.sh


# 디렉토리 없으면 생성
backup_dir=${__DIR__}/backup/`time_today`/
if [[ ! -d $backup_dir ]];then
    mkdir -p $backup_dir;
fi

# 만료일
day=14

# 날짜 계산
now=`time_unix`
expire=$(( 3600 * 24 * $day ))
expire=`date -d @$(($now - $expire )) +%Y%m%d`


dir=`dirname $backup_dir`
dir=`ls $dir`


for d in ${dir[@]};do
    # 만료 폴더 삭제
    if [[ $expire -gt $d ]];then
        rm -rf `dirname $backup_dir`/$d
    fi
done


# mysql DB 얻기
DB=`mysql -e 'show databases'`;

# 컬럼에 해당하는 Database , mysql 시스템 DB
ignore_db=('Database' 'information_schema' 'performance_schema' 'mysql')

for db in ${DB[@]};do

    # ignore_db 에 있으면 스킵
    array_in $db ${ignore_db[@]}
    if [[ $? -eq 0 ]];then
        continue;
    fi

    # 백업 실행
    sleep 1
    mysqldump --triggers --events --routines --single-transaction --compress $db \
    | sed "s/AUTO_INCREMENT=[0-9]*//" \
    | sed "s/\`${db}\`.//" \
    | sed "s/DEFINER=\`[a-z]*\`@\`\(localhost\|[0-9.]*\|%\)\`//" \
    > $backup_dir/$db.sql;
done



