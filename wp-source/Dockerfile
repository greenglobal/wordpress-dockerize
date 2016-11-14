FROM ubuntu:14.04

RUN apt-get update && apt-get install -y ssh git nginx

RUN mkdir -p /root/.ssh \
    && ssh-keyscan bitbucket.org >> /root/.ssh/known_hosts \
    && ssh-keyscan github.com >> /root/.ssh/known_hosts

ENV appDir /var/www/app

# Set the work directory
RUN mkdir -p ${appDir}
WORKDIR ${appDir}

COPY run.sh ./
COPY privatekey ./

RUN chmod 400 privatekey && eval "$(ssh-agent -s)" && ssh-add privatekey && git clone -b develop git@bitbucket.org:toancauxanh/gg-web-live.git greenglobal-website
WORKDIR /var/www/app/greenglobal-website

EXPOSE 3000
CMD sh /var/www/app/run.sh
