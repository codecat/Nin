FROM caddy:alpine

LABEL MAINTAINER="Melissa Geels"

# Install PHP-FPM and modules that Nin supports
RUN apk add php81-fpm php81-session php81-pgsql php81-mysqli php81-sqlite3 php81-pecl-apcu php81-opcache php81-mbstring

# Copy the actual Nin code
COPY . /var/www/nin

# Copy the Caddyfile from the server-configs directory
COPY ./server-configs/Caddyfile /etc/caddy/Caddyfile

# Start php-fpm and Caddy
CMD php-fpm81; caddy run --config /etc/caddy/Caddyfile --adapter caddyfile
