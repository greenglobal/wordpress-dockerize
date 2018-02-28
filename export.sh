#!/bin/bash
os="`uname`"
now=$(date +"%m_%d_%Y")
file="./db_$now.sql"
db_host_name=db
docker-compose exec $db_host_name sh -c 'export MYSQL_PWD="$MYSQL_PASSWORD"; exec mysqldump "$MYSQL_DATABASE" -u"$MYSQL_USER"' > $file
gzip $file
