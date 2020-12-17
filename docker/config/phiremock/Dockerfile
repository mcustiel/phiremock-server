FROM php:7.3-cli

RUN apt-get update && apt-get -y install git gzip wget libgcrypt20-dev zlib1g-dev libzip-dev zip bzip2 libbz2-dev libmcrypt-dev
RUN docker-php-ext-install zip bz2
RUN docker-php-ext-configure pcntl && docker-php-ext-install pcntl

# Setup composer
RUN EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig)" && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")" && \
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ] ; then >&2 echo 'ERROR: Invalid installer signature'; rm composer-setup.php; exit 1; fi && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    RESULT=$? && \
    rm composer-setup.php

RUN chmod +x /usr/local/bin/composer

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN mkdir -p -m 0777 /opt/composer
ENV COMPOSER_HOME=/opt/composer

RUN composer global require mcustiel/phiremock-server --prefer-dist --no-interaction --optimize-autoloader --apcu-autoloader

RUN mkdir -p -m 0777 /opt/phiremock/expectation-files

EXPOSE 80

CMD ["/opt/composer/vendor/bin/phiremock", "-d", "-p", "80", "-i", "0.0.0.0", "-e", "/opt/phiremock/expectation-files"]
