#!/bin/bash
os="`uname`"
now=$(date +"%m_%d_%Y")
file="./db_$now.sql"
db_host_name=db
docker-compose exec $db_host_name sh -c 'exec mysqldump "$MYSQL_DATABASE" -u"MYSQL_USER" -p"$MYSQL_PASSWORD"' > $file
gzip $file
