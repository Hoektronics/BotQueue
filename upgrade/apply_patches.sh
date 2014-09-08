#!/bin/sh

cd patches/
FILE_LIST=`ls *.php | sort`
for f in $FILE_LIST
do
  php "$f"
done
