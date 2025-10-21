FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev zip curl supervisor \
    && docker-php-ext-install pdo pdo_mysql zip bcmath pcntl


COPY  --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

# COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

CMD ["entrypoint.sh"]

# CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]

# CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]