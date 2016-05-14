#!/bin/sh

sh_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
properties_file=/home/coa/coa.properties
export_page=http://www.ducksmanager.net/remote/generer_csv_pour_export.php

cd ${sh_dir}

set +x
. ${properties_file}

echo "wget ${export_page}?mdp=$\{SECURITY_PASSWORD\}&csv=numeros -O export/numeros.csv" 1>&2
wget "${export_page}?mdp=${SECURITY_PASSWORD}&csv=numeros" -O export/numeros.csv

echo "wget ${export_page}?mdp=$\{SECURITY_PASSWORD\}&csv=auteurs_pseudos -O export/auteurs_pseudos.csv" 1>&2
wget "${export_page}?mdp=${SECURITY_PASSWORD}&csv=auteurs_pseudos" -O export/auteurs_pseudos.csv

echo "mysql -v --user=root --password=xxxxxxxx --default_character_set utf8 db301759616 --local_infile=1 < import_preferences_auteurs_pour_stats.sql" 1>&2
mysql -v --user=root --password=${DB_PASSWORD} --default_character_set utf8 db301759616 --local_infile=1 < import_preferences_auteurs_pour_stats.sql

set -x