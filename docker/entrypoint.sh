#!/bin/sh
set -e

[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=base64:' .env || php artisan key:generate --force

touch database/database.sqlite
php artisan migrate:fresh --seed --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-5107}"
