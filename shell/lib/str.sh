#!/bin/bash


#########################
# 문자열 길이 구하기
#########################
function str_len ()
{
	echo `echo $@ | wc -L`
	return 0 ;
}


#########################
# 대문자를 모두 소문자로
#########################
function str_lower ()
{
	echo `echo $@ | tr [:upper:] [:lower:]`
	return 0 ;
}


#########################
# 소문자를 모두 대문자로
#########################
function str_upper ()
{
	echo `echo $@ | tr [:lower:] [:upper:]`
	return 0 ;
}
