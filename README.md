sudo docker build --tag=greenglobal-wp-source:1.0 wp-source/
sudo docker build --tag=greenglobal-db:1.0 mysql/
sudo docker build --tag=greenglobal-admin-guid:1.0 phpmyadmin/

sudo docker run -d --name=greenglobal-db --restart=always greenglobal-db:1.0
sudo docker run -d --name=greenglobal-admin-guid --restart=always -p 3001:80 --link=greenglobal-db:db -e "NODE_ENV=development" greenglobal-admin-guid:1.0
sudo docker run --name=greenglobal-wp-source -p 80:80 --link=greenglobal-db:db -e "NODE_ENV=development" -v "/var/www/greenglobal.vn/toancauxanh.vn/public_html:/var/www/html" greenglobal-wp-source:1.0
