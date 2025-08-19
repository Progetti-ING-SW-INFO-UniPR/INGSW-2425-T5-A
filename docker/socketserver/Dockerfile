FROM php:8.0-alpine

COPY ./app /app
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./entrypoint.sh /entrypoint.sh

RUN ["chmod", "+x", "/entrypoint.sh"]

ENTRYPOINT /entrypoint.sh

EXPOSE 8000

CMD [ "php", "./app/server.php"]