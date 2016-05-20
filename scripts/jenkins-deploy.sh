#!/usr/bin/env bash

rm -f changeset.xml
rm -rf files changeset.txt
wget --auth-no-challenge --http-user=$API_USER --http-password=$API_PASS "$JENKINS_URL/api/xml?depth=2&xpath=/hudson/job[name='$JOB_NAME']/build[id='$BUILD_ID']/changeSet" --output-document=changeset.xml

xpath -q -e "//changeSet/item/path/file[not(../editType/text() = 'delete')]/text()" changeset.xml | uniq > changeset.txt

mkdir files
cd files

BASE=raw.github.com/bperel/DucksManager/master/
mkdir -p $BASE

wget -xi ../changeset.txt --base=https://$BASE


ls -la $BASE