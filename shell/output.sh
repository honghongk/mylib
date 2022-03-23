#!/bin/sh

source $pwd/lib/str.sh

function alert()
{
	local separate len separate_character ;

	separate='=';
	len=`str_len $@`;

	for (( i=0; i<$len ; i++ ));do
		separate_character=${separate_character}${separate}
	done


	echo '';
	echo $separate_character;
	echo '';
	echo $@;
	echo '';
	echo $separate_character;
	echo '';
}

function error ()
{
	alert $@

	exit 1;
}