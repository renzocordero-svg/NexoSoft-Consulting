FROM php:8.3-cli
RUN apt-get update && apt-get install -y git unzip libzip-dev libonig-dev libxml2-dev && docker-php-ext-install pdo_mysql mbstring zip && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist
COPY . .
RUN composer dump-autoload --no-dev --optimize && chmod +x docker/entrypoint.sh
ENTRYPOINT ["sh","docker/entrypoint.sh"]
