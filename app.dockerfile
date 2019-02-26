FROM php:7.2-fpm

RUN apt-get update && apt-get install -y mysql-client git zip unzip curl

RUN docker-php-ext-install pdo pdo_mysql
RUN apt update \
    && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd

# Change the working directory.
WORKDIR /var/www

# Installing composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get update && apt-get install -y gnupg

RUN useradd -ms /bin/bash dockeruser

WORKDIR /var/www
