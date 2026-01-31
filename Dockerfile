ARG FRANKENPHP_VERSION=php8.4

FROM node:lts-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js .
RUN npm run build

FROM dunglas/frankenphp:${FRANKENPHP_VERSION} AS deps

WORKDIR /app

RUN apt-get update && apt-get install -y git unzip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --no-autoloader

COPY . .
COPY --from=frontend /app/public/build /app/public/build
RUN composer dump-autoload --optimize

FROM dunglas/frankenphp:${FRANKENPHP_VERSION} AS development

WORKDIR /app

RUN install-php-extensions \
    pdo_pgsql \
    pcntl \
    intl

FROM dunglas/frankenphp:${FRANKENPHP_VERSION} AS production

WORKDIR /app

# Configure OPcache for production performance
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_file_override=1'; \
    } > /usr/local/etc/php/conf.d/opcache-production.ini

# Configure PHP settings for better performance
RUN { \
    echo 'max_execution_time=120'; \
    echo 'max_input_time=120'; \
    echo 'default_socket_timeout=120'; \
    echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/custom-php.ini

RUN install-php-extensions \
    pdo_pgsql \
    pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY --from=deps /app /app

EXPOSE 8000
ENTRYPOINT ["php", "artisan", "octane:frankenphp", "--workers=5", "--max-requests=250"]
