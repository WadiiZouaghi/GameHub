FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install pdo_pgsql intl opcache zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY . .

RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

RUN mkdir -p var/cache var/log public/uploads/avatars \
    && chown -R www-data:www-data var public/uploads \
    && chmod -R 775 var public/uploads

EXPOSE 9000

CMD ["php-fpm"]
