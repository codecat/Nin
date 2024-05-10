FROM caddy:alpine

LABEL MAINTAINER="Melissa Geels"

# Install PHP-FPM and modules that Nin supports
RUN apk add php82-fpm php82-session php82-pgsql php82-mysqli php82-sqlite3 php82-pecl-apcu php82-opcache php82-mbstring

# Make sure php-fpm doesn't clear environment variables
RUN printf "[www]\nclear_env = no" > /etc/php82/php-fpm.d/env.conf

# Copy the actual Nin code
COPY . /var/www/nin

# Copy the Caddyfile from the server-configs directory
COPY ./server-configs/Caddyfile /etc/caddy/Caddyfile

# Start php-fpm and Caddy
CMD php-fpm82; caddy run --config /etc/caddy/Caddyfile --adapter caddyfile
