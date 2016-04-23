#!/bin/sh
echo "Hello!"

file='91M_1.csv'
regex='"response":\[(.*)\]'

while IFS=',' read -a line; do
	url="http://etav2.kmb.hk/?action=geteta&lang=en&route=91M&bound=1&stop=${line[2]}&stop_seq=${line[0]}"
	text="`wget -qO- $url`"
	if [[ $text =~ $regex ]]
	then
		name="${BASH_REMATCH[1]}"
		echo $name;
	fi	
done < $file
