FROM mariadb:10.5

ENV MYSQL_ROOT_PASSWORD=rootpassword
ENV MYSQL_DATABASE=AstroAllies_DB
ENV MYSQL_USER=user
ENV MYSQL_PASSWORD=userpassword
ENV PSW_PEPE=vH@_a26A

RUN apt-get update && apt-get install -y \
    iputils-ping traceroute curl openssh-server && \
    mkdir /var/run/sshd

# Copia il file di configurazione del database
COPY ./setup /docker-entrypoint-initdb.d

EXPOSE 3306 22
