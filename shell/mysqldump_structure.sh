#!/bin/sh


# @@ 뷰가 일반 테이블로 백업됨

array_in ()
{
    local needle array value ;
    needle="${1}"; shift; array=("${@}")
    for value in ${array[@]}; do
		[ "${value}" == "${needle}" ] && return 0;
	done
    return 1;
}


# DB 목록
DB=(`mysql -e 'show databases;'`)

# 기본 DB
ignoreDB=('information_schema performance_schema mysql')


for i in ${!DB[*]} ; do
    # 맨처음 컬럼이름 스킵
    if [ $i -eq 0 ] ; then
        continue;
    fi

    # 기본디비 스킵
    array_in ${DB[$i]} $ignoreDB
    if [ $? -eq 0 ] ; then
        continue;
    fi

    # view 는 따로 해야함 테이블 취급됨

    # 스키마구조, 트리거, 이벤트, 함수, 프로시저, auto increment 초기화
    mysqldump \
    --no-data \
    --triggers \
    --events \
    --routines \
    --single-transaction \
    --compress \
    ${DB[$i]} \
    | sed 's/AUTO_INCREMENT=[0-9]*//' \
    | sed "s/\`${DB[$i]}\`.//" \
    | sed "s/DEFINER=\`[a-z]*\`@\`\(localhost\|[0-9.]*\|%\)\`//" \
    > ${DB[$i]}.sql
done

