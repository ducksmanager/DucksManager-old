#!/bin/sh
sh_dir="$PWD"
. ~/ducksmanager/inducks.properties
cd $isv_path
rm *.isv
rm *.sql
wget -rnd -l1 --no-parent http://coa.inducks.org/inducks/isv/
mv createtables.sql ..
cd $sh_dir
php clean_sql_inducks.php
cd $isv_path/..
mysql --user=root --password=$db_password --default_character_set utf8 coa --local_infile=1 < createtables_clean.sql
exit
cd $sh_dir
php insert_new_similar_edges.php

mysqldump --password=$db_password --user=root --databases db301759616 > /var/www/dump.sql
