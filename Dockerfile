FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
    git

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
COPY . /var/www
VOLUME /var/www
