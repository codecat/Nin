# Server configurations
This folder contains server configurations for several server types. These are useful if you need to manually configure your webserver.

If you're using the Docker image, you won't need to manually configure anything.

## Caddy
This is my personal recommended way of hosting Nin websites. [Caddy](https://caddyserver.com/) is a multi-purpose webserver that makes website hosting very easy and mostly maintenance free.

Nin runs with the default Caddy PHP configuration under php-fpm. For an example configuration, check out [`Caddyfile`](Caddyfile).

Caddy is also the new default for the Nin 2.0 docker image.

## Apache
This used to be the default way of hosting Nin websites. It still works well. You'll need `mod_rewrite` and some rewrite rules to point everything to `index.php`. See [`.htaccess`](.htaccess) for a functional example.

## Nginx
Nginx is the least tested environment for Nin. It used to work just fine, but I give no guarantees that the example configuration still works. Regardless, you can check out [`nginx.conf`](nginx.conf).
