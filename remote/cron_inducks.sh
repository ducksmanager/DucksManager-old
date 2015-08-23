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