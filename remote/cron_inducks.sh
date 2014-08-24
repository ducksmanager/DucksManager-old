#!/bin/sh
set +x
. /home/ducksmanager/ducksmanager.properties
set -x

cd $isv_path
rm *.isv
rm *.sql
wget -rNnd -l1 --no-parent http://coa.inducks.org/inducks/isv/
mv createtables.sql ..
cd $sh_dir
php import_inducks.php 'clean'
cd $isv_path/..

set +x
echo "mysql -v --user=root --password=xxxxxxxx --default_character_set utf8 coa --local_infile=1 < createtables_clean.sql" 1>&2
mysql -v --user=root --password=$db_password --default_character_set utf8 coa --local_infile=1 < createtables_clean.sql

removed_publications_text=$(php import_inducks.php 'check_removed_publications')
LEN=$(echo ${#removed_publications_text})
if [ ${LEN} -gt 0 ]; then
	set +x
	echo ${removed_publications_text} | mail -s "DucksManager - Magazines disparus" $admin_email
	echo "mail -s 'DucksManager - Magazines disparus' \$admin_email"
	set -x
fi

cd $sh_dir
php insert_new_similar_edges.php

set +x
echo "mysqldump --password=xxxxxxxx --user=root --databases db301759616 > /var/www/dump.sql" 1>&2
mysqldump --password=$db_password --user=root --databases db301759616 > /var/www/dump.sql
tar -cvzf /var/www/archive/dump-$(date +"%Y-%m-%d").tar.gz /var/www/dump.sql
set -x
