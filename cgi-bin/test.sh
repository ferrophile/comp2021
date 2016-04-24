#!/bin/sh
echo "Hello!"

file='91M_1.csv'
regex='"response":\[(.*)\]'
regex2='"ex":".* ([0-9]*):([0-9]*):([0-9]*)"'
len=0

declare -A data


while IFS=',' read -a line; do
	url="http://etav2.kmb.hk/?action=geteta&lang=en&route=91M&bound=1&stop=${line[2]}&stop_seq=${line[0]}"
	text="`wget -qO- $url`"
	if [[ $text =~ $regex ]]
	then
		entry="${BASH_REMATCH[1]}"
		IFS='}' read -a record <<< "$entry"
		data[${line[0]}, 0, 0]=${#record[@]}
		for (( i=0; i<=(${#record[@]}-1); i++ )); do
			if [[ ${record[$i]} =~ $regex2 ]]; then
				hour=${BASH_REMATCH[1]#0}
				min=${BASH_REMATCH[2]#0}
				sec=${BASH_REMATCH[3]#0}
				if (( $min > 9 )); then
					min=`expr $min - 10`
				else
					min=`expr $min + 50`
					hour=`expr $hour - 1`				
				fi
				data[${line[0]}, (($i+1)), 0]=$hour
				data[${line[0]}, (($i+1)), 1]=$min
				data[${line[0]}, (($i+1)), 2]=$sec
			fi
		done
	fi
	len=`expr $len + 1`
done < $file

for (( i=0; i<$len; i++ )); do
	for (( j=0; j<${data[$i, 0, 0]}; j++ )); do
		printf "${data[$i, (($j+1)), 0]}h, ${data[$i, (($j+1)), 1]}m, ${data[$i, (($j+1)), 2]}s; "
	done
	printf "\n"
done
