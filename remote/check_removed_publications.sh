#!/bin/sh
sh_dir=`pwd`/remote
set +x
. /home/ducksmanager/ducksmanager.properties
set -x

removed_publications_text=$(php ${sh_dir}/import_inducks.php 'check_removed_publications')
LEN=$(echo ${#removed_publications_text})
if [ ${LEN} -gt 0 ]; then
    set +x
    echo ${removed_publications_text} | mail -s "DucksManager - Magazines disparus" $admin_email
    echo "mail -s 'DucksManager - Magazines disparus' \$admin_email"
    set -x
fi
