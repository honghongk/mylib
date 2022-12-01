#!/bin/sh

###################
# 함수 있는지 확인
###################
function_exists()
{
    if [[ `type $1|grep "is a function"|wc -l` -gt 0 ]];then
        return 0;
    else
        return 1;
    fi
}
