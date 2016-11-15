FROM richarvey/nginx-php-fpm:latest

ENV WEBROOT /var/www/html
#ENV GIT_REPO https://binhqdgreenglobal@bitbucket.org/toancauxanh/gg-web-live.git
ENV GIT_EMAIL binhqd@greenglobal.vn
ENV GIT_NAME binhqdgreenglobal
#ENV SSH_KEY privatekey
#ENV GIT_USERNAME binhqdgreenglobal


# Set the work directory
WORKDIR /var/www/html

# COPY run.sh ./
COPY vhost.sample /etc/nginx/sites-enabled/default.conf
COPY cm_rules.nginx.sample /var/www/cm_rules.nginx

WORKDIR /var/www/html

EXPOSE 3000
