FROM php:7.3-fpm-alpine

WORKDIR /var/www/app

RUN docker-php-ext-install pdo pdo_mysql gd
