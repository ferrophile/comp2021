#!/bin/bash
echo "Content-type: text/html"
echo ""
echo '<html>'
echo '<head>'
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
echo '<title>Hello World</title>'
echo '</head>'
echo '<body>'

file='91M_1.csv'
regex='"response":\[(.*)\]'
regex2='"updated":([0-9]*)'
regex3='"ex":".* ([0-9]*):([0-9]*):([0-9]*)"'

declare -A data
declare -A lengths
declare -A names
declare -A updated
declare -A interval
declare -A dist

len=0
while IFS=',' read -a line; do
	url="http://etav2.kmb.hk/?action=geteta&lang=en&route=91M&bound=1&stop=${line[2]}&stop_seq=${line[0]}"
	text="`wget -qO- $url`"
	echo $text
	
	names[$len]=${line[1]}
	interval[$len]=${line[3]}
	dist[$len]=${line[4]}
	
	if [[ $text =~ $regex2 ]]; then
		stamp="${BASH_REMATCH[1]}"
		updated[$len]=$(($stamp / 1000 + 28800))
		updated[$len]=$((${updated[$len]} % 86400))
	fi
	
	if [[ $text =~ $regex ]]
	then
		entry="${BASH_REMATCH[1]}"
		IFS='}' read -a record <<< "$entry"
		lengths[$len]=${#record[@]}
		for (( i=0; i<=(${#record[@]}-1); i++ )); do
			if [[ ${record[$i]} =~ $regex3 ]]; then
				hour=${BASH_REMATCH[1]#0}
				min=${BASH_REMATCH[2]#0}
				sec=${BASH_REMATCH[3]#0}
				if (( $min > 9 )); then
					min=`expr $min - 10`
				else
					min=`expr $min + 50`
					hour=`expr $hour - 1`				
				fi
				if (( i==0 )); then
					echo "${line[1]}, $hour, $min, $sec"
				fi
				data[${line[0]}, $i]=`expr $hour \* 3600 + $min \* 60 + $sec`
			fi
		done
	fi
	len=`expr $len + 1`
done < $file

res=""
for (( i=1; i<$len; i++ )); do
	j=`expr $i - 1`
	if (( ${data[$i, 0]} < ${data[$j, 0]} )); then
		if (( ${data[$i, 0]} > ${updated[$i]} )); then
			remain=$((${data[$i, 0]} - ${updated[$i]}))
		else
			remain=0
		fi
		offset=$((${dist[$i]} - ${dist[$j]}))
		offset=$(($offset * $remain / ${interval[$i]}))
		offset=$((${dist[$i]} - $offset))
		res="${res}<img src=\"circle.png\" style=\"position: fixed; top: 45; left: ${offset};\"/>"
	fi
done
echo "$res"
echo 'Hello World!'
echo '</body>'
echo '</html>'