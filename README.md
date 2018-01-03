## Build network
```sh
sudo docker network create tcx-wordpress
```

## Run Docker compose
```sh
sudo docker-compose up -d
```

## Generate a htpasswd file:
```sh
sudo docker run --rm -ti xmartlabs/htpasswd ggadmin greenglobal@?! > wordpress/.htpasswd
username: ggadmin
password: greenglobal@?!
```

## Admin info
```sh
url: http://localhost:8080/gglogin
username: ggadmin
password: greenglobal@?!
```

## Install plugin
### WP Fastest Cache
```sh
sudo docker exec wp-source bash -c "wp plugin install wp-fastest-cache --activate"
```

## Using Saga WordPress Starter Theme
```sh
git clone https://github.com/roots/sage <path to themes dir>/<theme name>
cd <path to themes dir>/<theme name>
yarn
composer install
yarn run build:production
```
## Active theme
```sh
sudo docker exec wp-source bash -c "wp theme activate <theme name>"
```

## Installation video
[![Installation video](https://img.youtube.com/vi/ahr1CUHAO7c/0.jpg)](https://www.youtube.com/watch?v=ahr1CUHAO7c)
