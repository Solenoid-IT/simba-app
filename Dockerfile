FROM ubuntu:24.04



ENV PMA_USER="admin"
ENV PMA_PASS="pass"



RUN apt update
RUN apt install -y nano
RUN apt install -y curl
RUN apt install -y apache2
RUN apt install -y mysql-server
RUN apt install -y php8.3 php8.3-fpm php8.3-curl php8.3-mysql php8.3-zip php8.3-gd php8.3-intl php8.3-mbstring
RUN apt install -y composer
RUN apt install -y nodejs
RUN apt install -y npm
RUN apt install -y redis-server
RUN apt install -y cron
RUN apt install -y supervisor



RUN a2enmod rewrite headers setenvif proxy proxy_fcgi proxy_http proxy_http2 proxy_wstunnel alias ssl http2



RUN echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2" | debconf-set-selections
RUN echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections



RUN DEBIAN_FRONTEND=noninteractive apt-get install -y phpmyadmin



COPY docker-setup/conf/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker-setup/conf/phpmyadmin.conf /etc/apache2/conf-enabled/phpmyadmin.conf
COPY docker-setup/conf/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf
COPY docker-setup/conf/redis.conf /etc/redis/redis.conf
COPY docker-setup/conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf



RUN mkdir -p /var/lib/redis
VOLUME "/var/lib/redis"



COPY docker-setup/conf/simba-cron /etc/cron.d/simba-cron
RUN chmod 0644 /etc/cron.d/simba-cron && touch /var/log/cron.log
RUN crontab /etc/cron.d/simba-cron



COPY src/ /app



COPY docker-setup/setup /usr/local/bin/docker-setup
RUN chmod +x /usr/local/bin/docker-setup



EXPOSE 80 443 3306 3001 6379 5173



VOLUME [ "/app", "/var/lib/mysql" ]



CMD [ "/usr/local/bin/docker-setup" ]