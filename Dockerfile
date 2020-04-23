FROM php:7-apache
MAINTAINER Melissa Geels
RUN a2enmod rewrite
COPY . /var/www/nin
