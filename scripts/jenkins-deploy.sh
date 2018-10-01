#!/usr/bin/env bash

rm -rf toUpload changeset.txt

previous_commitid="`wget -qO- https://ducksmanager.net/deployment_commit_id.txt`"
commitid=`git rev-parse HEAD`

git diff --diff-filter=d --name-only ${previous_commitid} > changeset.txt
echo "index.php" >> changeset.txt
echo "Change set : " && cat changeset.txt

mkdir toUpload

cat changeset.txt | while read -r file; do
  cp --parents $file toUpload
done

sed -i "s/VERSION/$commitid/g" toUpload/index.php

ls -la toUpload