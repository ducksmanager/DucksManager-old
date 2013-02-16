#!/bin/sh
cd /var/www/inducks/isv
rm *.isv
rm *.sql
wget -rnd -l1 --no-parent http://coa.inducks.org/inducks/isv/
cd ..
mv isv/createtables.sql ..
cd ..
php clean_sql_inducks.php
cd inducks
mysql --user=root --password=xxxxxx --default_character_set utf8 coa < createtables_clean.sql
cd ..
php insert_new_similar_edges.php

mysqldump --password=xxxxxx --user=root --databases db301759616 > /var/www/dump.sql