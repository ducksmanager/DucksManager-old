#!/bin/sh
. /home/ducksmanager/ducksmanager.properties

content=$(wget ducksmanager.net -q -O -)

LEN=$(echo ${#content})
if [ $LEN -lt 1000 ]; then
	set +x
	echo "" | mail -s "DucksManager semble indisponible (homepage : $LEN caracteres)" $admin_email
	echo "mail -s 'DucksManager semble indisponible (homepage : $LEN caracteres)' \$admin_email"
	set -x
fi
