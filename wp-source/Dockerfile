FROM richarvey/nginx-php-fpm:latest

ENV WEBROOT /var/www/html
#ENV GIT_REPO https://binhqdgreenglobal@bitbucket.org/toancauxanh/gg-web-live.git
ENV GIT_EMAIL binhqd@greenglobal.vn
ENV GIT_NAME binhqdgreenglobal
#ENV SSH_KEY privatekey
#ENV GIT_USERNAME binhqdgreenglobal
ENV WP_DBNAME gg
ENV WP_USER gg
ENV WP_PASS gg123456
ENV WP_HOST db
ENV WP_CACHEHOME /var/www/html/wp-content/plugins/wp-super-cache/

RUN mkdir -p /root/.ssh \
    && ssh-keyscan bitbucket.org >> /root/.ssh/known_hosts \
    && ssh-keyscan github.com >> /root/.ssh/known_hosts

# Set the work directory
rm -rf /var/www/html
WORKDIR /var/www

COPY privatekey ./

RUN chmod 400 privatekey && eval "$(ssh-agent -s)" && ssh-add privatekey && git clone -b develop git@bitbucket.org:toancauxanh/gg-web-live.git html


# COPY run.sh ./
COPY vhost.sample /etc/nginx/sites-enabled/default.conf
COPY cm_rules.nginx.sample /var/www/cm_rules.nginx

WORKDIR /var/www/html

EXPOSE 3000
