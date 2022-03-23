#!/bin/sh

######################
# 배열에 있는지 확인
######################
array_in ()
{
    local needle array value ;
    needle="${1}"; shift; array=("${@}")
    for value in ${array[@]}; do
		[ "${value}" == "${needle}" ] && return 0;
	done
    return 1;
}