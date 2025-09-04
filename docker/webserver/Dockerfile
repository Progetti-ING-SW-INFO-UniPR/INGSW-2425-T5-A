FROM php:8.1-apache

# Abilita i moduli e installa strumenti
RUN a2enmod ssl rewrite proxy proxy_http proxy_wstunnel
RUN apt-get update && apt-get install -y \
    iputils-ping traceroute curl openssh-server default-mysql-client && \
    docker-php-ext-install mysqli && \
	docker-php-ext-install sockets && \
    docker-php-ext-enable mysqli && \
    mkdir /var/run/sshd -p

# Copia certificati SSL
COPY ./certs /etc/ssl/certs

# Configura Apache per usare SSL
COPY ./default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite default-ssl && a2ensite 000-default

# Copia i file dell'app web
COPY ./AstroAllies /var/www/html/

# Espone porte HTTP, HTTPS e SSH
EXPOSE 80 443 22

CMD ["apache2-foreground"]

