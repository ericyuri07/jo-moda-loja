# Imagem de produção: Laravel + FrankenPHP (Caddy embutido, HTTPS automático).
# O mesmo container entrega o site Flutter (/) e a API (/api).
FROM dunglas/frankenphp:1-php8.4

RUN install-php-extensions pdo_mysql zip opcache intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { \
        echo 'upload_max_filesize=4M'; \
        echo 'post_max_size=8M'; \
        echo 'opcache.enable=1'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'expose_php=Off'; \
    } > "$PHP_INI_DIR/conf.d/zz-jomodas.ini"

WORKDIR /app

# Dependências primeiro, para aproveitar o cache do Docker.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN rm -f .env \
    && composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && mkdir -p storage/app/public/products storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY deploy/Caddyfile /etc/jomodas/Caddyfile
COPY deploy/entrypoint.sh /usr/local/bin/jomodas-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/jomodas-entrypoint && chmod +x /usr/local/bin/jomodas-entrypoint

EXPOSE 80 443

ENTRYPOINT ["jomodas-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/jomodas/Caddyfile"]
