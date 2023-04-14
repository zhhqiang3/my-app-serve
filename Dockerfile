FROM php:7.4-fpm
WORKDIR /var/www/html
COPY . /var/www/html

RUN apt-get update \
      && apt-get install -y zlib1g-dev libicu-dev g++ \
      && docker-php-ext-configure intl \
      && docker-php-ext-install intl
