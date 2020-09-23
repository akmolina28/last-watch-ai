FROM php:7.4-fpm

WORKDIR /var/www/app

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        smbclient \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install pdo pdo_mysql gd

#COPY ./src ./

#RUN chown -R www-data:www-data .
