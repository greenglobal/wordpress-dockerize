FROM richarvey/nginx-php-fpm:1.1.6

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

# setup WP CLI
RUN cd ~
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

RUN wp --info --allow-root

# setup composer
RUN curl -sS https://getcomposer.org/installer | php -- --install- dir=/usr/local/bin --filename=composer
RUN composer

WORKDIR /var/www/html

EXPOSE 3000
