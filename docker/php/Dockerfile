FROM php:8.3-fpm

# Estensioni di sistema richieste da Symfony / Doctrine / PhpSpreadsheet
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache-custom.ini

# composer.json/composer.lock vengono copiati per primi cosi' la cache
# Docker riutilizza il layer "composer install" finche' non cambiano.
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --no-scripts --no-progress --prefer-dist \
    || composer update --no-interaction --no-scripts --no-progress --prefer-dist

COPY . .

RUN composer dump-autoload --optimize || true

RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var

CMD ["php-fpm"]
