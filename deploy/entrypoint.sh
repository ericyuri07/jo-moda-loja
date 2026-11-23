#!/bin/sh
# Roda a cada vez que o container sobe: espera o MySQL, aplica migrations e
# gera os caches do Laravel com as variáveis do .env de produção.
set -e

cd /app

mkdir -p storage/app/public/products storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage

echo "Aguardando o MySQL..."
tries=0
until php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Throwable $e) { exit(1); }'; do
    tries=$((tries + 1))
    if [ "$tries" -ge 60 ]; then
        echo "MySQL não respondeu em 2 minutos." >&2
        exit 1
    fi
    sleep 2
done

php artisan migrate --force
php artisan optimize

exec "$@"
