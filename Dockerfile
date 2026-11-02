FROM php:8.3-cli-alpine

RUN apk add --no-cache unzip sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && rm -f .env database/database.sqlite \
    && chown www-data:www-data /app \
    && chown -R www-data:www-data storage bootstrap/cache database

ENV APP_ENV=production \
    APP_DEBUG=false \
    PHP_CLI_SERVER_WORKERS=4 \
    PORT=5107

USER www-data
EXPOSE 5107
ENTRYPOINT ["docker/entrypoint.sh"]
