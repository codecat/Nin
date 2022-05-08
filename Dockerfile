FROM caddy:alpine

LABEL MAINTAINER="Melissa Geels"

# Install PHP-FPM and modules that Nin supports
RUN apk add php8-fpm php8-session php8-pgsql php8-mysqli php8-sqlite3 php8-pecl-apcu php8-opcache php8-mbstring

# Copy the actual Nin code
COPY . /var/www/nin

# Copy the Caddyfile from the server-configs directory
COPY ./server-configs/Caddyfile /etc/caddy/Caddyfile

# Start php-fpm and Caddy
CMD php-fpm8; caddy run --config /etc/caddy/Caddyfile --adapter caddyfile
