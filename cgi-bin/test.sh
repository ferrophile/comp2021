#!/bin/sh
echo "Hello!"

file='91M_1.csv'
regex='"response":\[(.*)\]'
regex2='"ex":".* ([0-9]*):([0-9]*):([0-9]*)"'

while IFS=',' read -a line; do
	url="http://etav2.kmb.hk/?action=geteta&lang=en&route=91M&bound=1&stop=${line[2]}&stop_seq=${line[0]}"
	text="`wget -qO- $url`"
	if [[ $text =~ $regex ]]
	then
		entry="${BASH_REMATCH[1]}"
		IFS='}' read -a record <<< "$entry"
		echo ${line[1]}
		for (( i=0; i<=(${#record[@]}-1); i++ )); do
			if [[ ${record[$i]} =~ $regex2 ]]
			then
				hour=${BASH_REMATCH[1]}
				min=${BASH_REMATCH[2]}
				sec=${BASH_REMATCH[3]}
				if [[ $min > '09' ]]
				then
					min=`expr $min - 10`
				else
					min=`expr $min + 50`
					hour=`expr $hour - 1`				
				fi
				echo "$hour:$min:$sec"
			fi
		done
	fi	
done < $file
