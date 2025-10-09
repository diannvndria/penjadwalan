# FROM node:lts-alpine AS build

# WORKDIR /app

# COPY package.json package-lock.json ./
# RUN npm ci
# COPY . .
# RUN npm run build

FROM dunglas/frankenphp:php8.4-bookworm AS production

RUN install-php-extensions \
    pdo_pgsql \
    pcntl

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

ENV SERVER_NAME=:80

# COPY --from=build /app/public/build /app/public/build

# RUN apk add --no-cache git unzip

# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# COPY composer.json composer.lock /app/

# RUN composer install \
#     --no-interaction \
#     --no-plugins \
#     --no-scripts \
#     --prefer-dist \
# 		--optimize-autoloader

COPY . /app

ENTRYPOINT ["php", "artisan", "octane:frankenphp"]
