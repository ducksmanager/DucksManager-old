#!/bin/sh
set +x
. /home/ducksmanager/ducksmanager.properties
set -x

set +x
echo "mysqldump --password=xxxxxxxx --user=root --databases db301759616 > /var/www/dump.sql" 1>&2
mysqldump --password=${db_password} --user=root --databases db301759616 > /var/www/dump.sql
tar -cvzf /var/www/archive/dump-$(date +"%Y-%m-%d").tar.gz /var/www/dump.sql
set -x
