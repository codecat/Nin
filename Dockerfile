FROM php:8-apache

LABEL MAINTAINER="Melissa Geels"

# Enable routing using the default .htaccess as a template
WORKDIR /etc/apache2/conf-available
COPY .htaccess ./nin_template.conf
RUN (echo "<Directory /var/www/html>"; \
      cat nin_template.conf; \
      echo "</Directory>" \
     ) > nin.conf
RUN a2enconf nin

# Enable APCu
RUN pecl install APCu && docker-php-ext-enable apcu

# Enable MySQLi
RUN docker-php-ext-install mysqli

# Enable opcache to improve performance
RUN docker-php-ext-enable opcache

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy the actual Nin code
COPY . /var/www/nin
