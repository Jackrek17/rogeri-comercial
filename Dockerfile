FROM php:8.2-apache

WORKDIR /var/www/html

# Keep Apache simple for local development.
RUN a2enmod rewrite

COPY . /var/www/html

EXPOSE 80
