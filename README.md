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

sudo docker run --name=wp-source -p 80:80 --link=wp-db:db \
-e "NODE_ENV=development" \
-v "/var/www/wp-source:/var/www/html" wp-source:1.0
```
