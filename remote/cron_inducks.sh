#!/bin/sh
isv_path=$1
inducks_path=${isv_path}/..

sh_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
properties_file=/home/coa/coa.properties

set +x
. ${properties_file}
set -x

cd ${inducks_path}
rm -rf *
wget http://coa.inducks.org/inducks/isv.7z && 7zr x isv.7z && rm isv.7z
for f in ${isv_path}/*.isv; do iconv -f utf-8 -t utf-8 -c "$f" > "$f.clean" && mv -f "$f.clean" "$f"; done # Ignore lines with invalid UTF-8 characters
mv ${isv_path}/createtables.sql ${inducks_path}

cd ${sh_dir}
php import_inducks.php 'clean' "${properties_file}" "${isv_path}"

cd ${inducks_path}
set +x
echo "mysql -v --user=root --password=xxxxxxxx --default_character_set utf8 coa --local_infile=1 < createtables_clean.sql" 1>&2
mysql -v --user=root --password=$DB_PASSWORD --default_character_set utf8 coa --local_infile=1 < createtables_clean.sql
