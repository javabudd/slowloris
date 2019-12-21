FROM php:7.4-zts

RUN mkdir /slowloris

COPY slowloris.php /slowloris/
COPY composer.json /slowloris/
COPY composer.lock /slowloris/
COPY entrypoint.sh /

# Install and enable parallel
RUN pecl install parallel-beta && \
    echo 'extension=parallel' >> /usr/local/etc/php/conf.d/parallel.ini

# Install and enable sockets
RUN docker-php-ext-install sockets
RUN docker-php-ext-enable sockets

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'baf1608c33254d00611ac1705c1d9958c817a1a33bce370c0595974b342601bd80b92a3f46067da89e3b06bff421f182') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');"

RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
