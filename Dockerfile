FROM php:8-apache

LABEL MAINTAINER="Melissa Geels"

# Install Postgres
RUN apt-get update && apt-get install -y libpq-dev && rm -rf /var/lib/apt/lists/*

# Enable APCu
RUN pecl install APCu && docker-php-ext-enable apcu

# Enable MySQLi
RUN docker-php-ext-install mysqli

# Enable Postgres
RUN docker-php-ext-install pgsql

# Enable opcache to improve performance
RUN docker-php-ext-enable opcache

# Enable mod_rewrite
RUN a2enmod rewrite

# Enable routing using the default .htaccess as a template
WORKDIR /etc/apache2/conf-available
COPY .htaccess ./nin_template.conf
RUN (echo "<Directory /var/www/html>"; \
      cat nin_template.conf; \
      echo "</Directory>" \
     ) > nin.conf
RUN a2enconf nin

# Copy the actual Nin code
COPY . /var/www/nin
