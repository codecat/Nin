FROM dunglas/frankenphp
RUN install-php-extensions pgsql mysqli sqlite3 apcu opcache
RUN cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY . /nin
