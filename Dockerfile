FROM php:7.3-apache
MAINTAINER Bruno Perel

RUN a2enmod rewrite

RUN apt-get update \
 && apt-get install -y \
      git wget unzip \
      libpng-dev libfreetype6-dev libmcrypt-dev libjpeg-dev libpng-dev \
 && apt-get clean

RUN docker-php-ext-configure gd \
  --enable-gd-native-ttf \
  --with-freetype-dir=/usr/include/freetype2 \
  --with-png-dir=/usr/include \
  --with-jpeg-dir=/usr/include

RUN docker-php-ext-install opcache

RUN pecl install xdebug-2.7.2 \
 && docker-php-ext-enable xdebug
