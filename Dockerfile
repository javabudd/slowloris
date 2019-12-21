FROM php:7.4-zts

RUN mkdir /slowloris

COPY ./ /slowloris/

# Install and enable parallel
RUN pecl install parallel-beta && \
    echo 'extension=parallel' >> /usr/local/etc/php/conf.d/parallel.ini

# Install and enable sockets
RUN docker-php-ext-install sockets

RUN chmod +x /slowloris/entrypoint.sh

ENTRYPOINT ["/slowloris/entrypoint.sh"]
