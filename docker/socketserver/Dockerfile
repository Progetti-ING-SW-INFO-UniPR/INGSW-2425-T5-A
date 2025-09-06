FROM php:8.1-alpine

COPY ./app /app
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install mysqli && \
    docker-php-ext-enable mysqli 

COPY ./entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh \
    && apk add --no-cache dos2unix \
    && dos2unix /entrypoint.sh

RUN ["chmod", "+x", "/entrypoint.sh"]

ENTRYPOINT /entrypoint.sh

EXPOSE 8000

CMD ["php", "./app/server.php"]