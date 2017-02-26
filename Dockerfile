FROM php:7.0-apache
MAINTAINER Bruno Perel

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
      git wget unzip mariadb-client \
      libpng-dev libfreetype6-dev libmcrypt-dev libpng12-dev libjpeg-dev libpng-dev

RUN docker-php-ext-configure gd \
  --enable-gd-native-ttf \
  --with-freetype-dir=/usr/include/freetype2 \
  --with-png-dir=/usr/include \
  --with-jpeg-dir=/usr/include

RUN docker-php-ext-install mysqli gd pdo pdo_mysql opcache

RUN cd /usr/src && \
    wget http://xdebug.org/files/xdebug-2.5.0.tgz && \
    tar -xvzf xdebug-2.5.0.tgz && \
    cd xdebug-2.5.0 && \
    phpize && \
    ./configure && \
    make && \
    cp modules/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20151012 && \
    bash -c "echo -e \"zend_extension = /usr/local/lib/php/extensions/no-debug-non-zts-20151012/xdebug.so\nxdebug.remote_enable=1\nxdebug.remote_host=192.168.0.16\"" > /usr/local/etc/php/php.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir -p /var/www/html/DucksManager/edges && chown -R www-data:www-data /var/www/html/DucksManager/edges && chmod a+w -R /var/www/html/DucksManager/edges