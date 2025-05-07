FROM php:8.1-apache

# Abilita i moduli per HTTPS e PHP
RUN a2enmod ssl
RUN apt-get update && apt-get install -y \
    iputils-ping traceroute curl openssh-server default-mysql-client && \
    docker-php-ext-install mysqli && \
    docker-php-ext-enable mysqli && \
    a2enmod ssl rewrite && \
    mkdir /var/run/sshd

# Aggiungi il certificato SSL (sostituisci con i tuoi certificati reali)
# Aggiungi il certificato SSL
#COPY ./certs /etc/ssl/certs/
#COPY ./certs/ssl-params.conf /etc/ssl/

# Configura Apache per SSL
#COPY ./apache-ssl.conf /etc/apache2/sites-available/default-ssl.conf
#RUN a2ensite default-ssl

# Copia i file dell'app web (HTML, CSS, PHP, JS)
COPY ./AstroAllies /var/www/html/

EXPOSE 80 443 22

# Permetti l'esecuzione di ping, traceroute, curl e SSH
RUN service apache2 restart

CMD ["apache2-foreground"]
