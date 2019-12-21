FROM php:7.4-zts

COPY slowloris.php /

RUN pecl install parallel-beta && \
    echo 'extension=parallel' >> /usr/local/etc/php/conf.d/parallel.ini
