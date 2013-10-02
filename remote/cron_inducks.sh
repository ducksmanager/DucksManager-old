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
php clean_sql_inducks.php
cd $isv_path/..

set +x
echo "mysql -v --user=root --password=xxxxxxxx --default_character_set utf8 coa --local_infile=1 < createtables_clean.sql" 1>&2
mysql -v --user=root --password=$db_password --default_character_set utf8 coa --local_infile=1 < createtables_clean.sql
set -x

cd $sh_dir
php insert_new_similar_edges.php

set +x
echo "mysqldump --password=xxxxxxxx --user=root --databases db301759616 > /var/www/dump.sql" 1>&2
mysqldump --password=$db_password --user=root --databases db301759616 > /var/www/dump.sql
set -x
