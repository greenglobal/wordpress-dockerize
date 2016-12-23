## Installing WordPress CLI
### download the wp-cli.phar file using wget or curl
```sh
cd ~
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
php wp-cli.phar --info
```

To use WP-CLI from the command line by typing wp, make the file executable and move it to somewhere in your PATH. For example:
```sh
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
```

If WP-CLI was installed successfully, you should see something like this when you run wp --info:
```
$ wp --info
PHP binary:    /usr/bin/php5
PHP version:    5.5.9-1ubuntu4.14
php.ini used:   /etc/php5/cli/php.ini
WP-CLI root dir:        /home/wp-cli/.wp-cli
WP-CLI packages dir:    /home/wp-cli/.wp-cli/packages/
WP-CLI global config:   /home/wp-cli/.wp-cli/config.yml
WP-CLI project config:
WP-CLI version: 1.0.0
```

## Build images
```sh
sudo docker build --tag=wp-source:1.0 wp-source/
sudo docker build --tag=wp-db:1.0 mysql/
sudo docker build --tag=mysql-admin-guid:1.0 phpmyadmin/
```

```sh
sudo docker run -d --name=wp-db --restart=always wp-db:1.0

sudo docker run -d --name=mysql-admin-guid --restart=always -p 3001:80 --link=wp-db:db \
-e "NODE_ENV=development" mysql-admin-guid:1.0

sudo docker run --name=wp-source -p 8080:80 --link=wp-db:db \
-e "NODE_ENV=development" \
-v "/var/www/wordpress/wordpress-dockerize/wp4.7:/var/www/html" wp-source:1.0
```
