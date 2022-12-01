#!/bin/sh



__FILE__=`realpath $0`
__DIR__=`dirname $__FILE__`
__LIB__=${__DIR__}/lib

source ${__LIB__}/array.sh
source ${__LIB__}/str.sh



# 사용률 확인

i=0
df | while read -r line;do
    i=$((i+1))
    if [[ $i == 1 ]];then
        # col=($line);
        continue;
    else
        row=($line);
    fi

    len=`str_len ${row[4]}`
    per=${row[4]:0:$(($len - 1))}

    if [[ $per > 50 ]];then
        echo '디스크 사용률 절반넘음' ${row[0]} $per%
    fi

done
