FROM mysql:8.0.0

MAINTAINER binhqd

ENV MYSQL_ROOT_PASSWORD 123456
ENV MYSQL_DATABASE wp
ENV MYSQL_USER wp
ENV MYSQL_PASSWORD 123456

COPY dump.sql.gz /docker-entrypoint-initdb.d/dump.sql.gz
COPY script.sql /docker-entrypoint-initdb.d/script.sql

EXPOSE 3306
