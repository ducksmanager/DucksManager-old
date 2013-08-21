#!/bin/bash

content=$(wget ducksmanager.net -q -O -)

LEN=$(echo ${#content})
if [ $LEN -lt 1000 ]; then
	echo "" | mail -s "DucksManager semble indisponible (homepage : $LEN caracteres)" xxxxxxxxxx@gmail.com
fi