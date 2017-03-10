## Build images
```sh
sudo docker build --tag=wp-source:1.0 wp-source/
sudo docker build --tag=wp-db:1.0 mysql/
sudo docker build --tag=mysql-admin-guid:1.0 phpmyadmin/
```

## Run images
```sh
sudo docker run -d --name=wp-db --restart=always wp-db:1.0

sudo docker run -d --name=mysql-admin-guid --restart=always -p 3001:80 --link=wp-db:db \
-e "NODE_ENV=development" mysql-admin-guid:1.0

sudo docker run --name=wp-source -p 8080:80 --link=wp-db:db \
-e "NODE_ENV=development" \
-v "/var/www/wordpress/wordpress-dockerize/wp4.7:/var/www/html" wp-source:1.0
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
