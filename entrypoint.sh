#!/bin/sh
set -e
echo "APP_MODE=$APP_MODE, PORT=${PORT}"
if [ "$APP_MODE" = "reverb" ]; then
    exec php artisan reverb:start --host=0.0.0.0 --port=${PORT:-8080}
else
    php artisan migrate:fresh --force
    php artisan db:seed --force
    exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
fi
