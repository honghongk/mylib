#!/bin/sh


############################################
# 무조건 띄어쓰기구분 때문에
# 배열 넘기면 각각의 파라미터로 넘어가서
# $@로 전체 받고 처리해야함
############################################

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


######################
# 배열의 키 출력
######################
array_key()
{
    array=($@);
    echo ${!array[@]}
    return 0;
}


######################
# 배열의 값 출력
######################
array_value()
{
    local array;
    array=($@);
    echo ${array[@]}
    return 0;
}


######################
# 배열의 길이 출력
######################
array_length()
{
    local array;
    array=($@);
    echo ${#array[@]}
    return 0;
}


######################
# 배열 구분자와 합치기
######################
array_join()
{
    local delimiter array value length i res;
    delimiter="${1}"; shift; array=("${@}")
    length=`array_length $@`

    res=''
    i=1
    for value in ${array[@]}; do
        if [[ $length == $i ]];then
            res+="${value}";
        else
		    res+="${value}${delimiter}";
        fi
        i=$(($i+1))
	done
    echo $res
    return 0;
}


######################
# 배열 구분자로 분리
######################
array_split()
{
    local delimiter str i char res;
    delimiter=$1;
    str=$2;

    res=''
    for (( i=0; i < ${#str}; i++ ));do
        char=${str:$i:1}
        if [[ $char == $delimiter ]];then
            res+=$IFS
        else
            res+=$char
        fi
    done
    echo $res
    return 0;
}
