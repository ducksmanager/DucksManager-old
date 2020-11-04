FROM php:7.3-apache
MAINTAINER Bruno Perel

RUN a2enmod rewrite

RUN apt-get update \
 && apt-get install -y \
      git wget unzip \
      libpng-dev libfreetype6-dev libmcrypt-dev libjpeg-dev libpng-dev \
 && apt-get clean

RUN docker-php-ext-configure gd \
  --with-freetype-dir=/usr/include/freetype2 \
  --with-png-dir=/usr/include \
  --with-jpeg-dir=/usr/include \
 && docker-php-ext-install opcache gd

COPY . /var/www/html

ARG COMMIT_HASH
ENV VERSION ${COMMIT_HASH}

RUN sed -i "s/VERSION/$VERSION/g" /var/www/html/index.php

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD composer install && apache2-foreground
