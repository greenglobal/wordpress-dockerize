FROM richarvey/nginx-php-fpm:latest

ENV WEBROOT /var/www/html

ENV MYSQL_DATABASE wp
ENV MYSQL_USER wp
ENV MYSQL_PASSWORD 123456
ENV MYSQL_HOST db


# Set the work directory
WORKDIR /var/www

# COPY run.sh ./
COPY vhost.sample /etc/nginx/sites-enabled/default.conf
COPY cm_rules.nginx.sample /var/www/cm_rules.nginx

WORKDIR /var/www/html

EXPOSE 3000
