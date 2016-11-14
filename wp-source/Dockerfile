FROM richarvey/nginx-php-fpm

#RUN apt-get update && apt-get install -y ssh git nginx

#RUN mkdir -p /root/.ssh \
#    && ssh-keyscan bitbucket.org >> /root/.ssh/known_hosts \
#    && ssh-keyscan github.com >> /root/.ssh/known_hosts

ENV appDir /var/www/html
ENV WEBROOT /var/www/html
#ENV GIT_REPO https://binhqdgreenglobal@bitbucket.org/toancauxanh/gg-web-live.git
ENV GIT_EMAIL binhqd@greenglobal.vn
ENV GIT_NAME binhqdgreenglobal
#ENV SSH_KEY privatekey
#ENV GIT_USERNAME binhqdgreenglobal


# Set the work directory
#RUN mkdir -p /var/www
WORKDIR /var/www/html

COPY run.sh ./
COPY privatekey ./
# COPY vhost /etc/nginx/sites-enabled/default

# RUN chmod 400 privatekey && eval "$(ssh-agent -s)" && ssh-add privatekey && git clone -b develop git@bitbucket.org:toancauxanh/gg-web-live.git greenglobal-website
WORKDIR /var/www/html

EXPOSE 3000
#ENTRYPOINT ["sh", "/var/www/app/run.sh"]
