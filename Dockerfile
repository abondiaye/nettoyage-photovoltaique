# Build stage
FROM php:8.2-fpm-alpine AS builder

RUN apk add --no-cache \
    postgresql-dev \
    libpq \
    git \
    curl \
    oniguruma-dev \
    libxml2-dev \
    openssl-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    curl \
    mbstring \
    xml \
    opcache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .

RUN php bin/console asset-map:compile --env=prod 2>/dev/null || true

# Production stage
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    postgresql-libs \
    libpq \
    supervisor \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    mbstring \
    xml \
    opcache

# Production PHP config
RUN echo 'opcache.enable=1' >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo 'opcache.revalidate_freq=0' >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo 'opcache.validate_timestamps=0' >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo 'opcache.memory_consumption=256' >> /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /app

COPY --from=builder /app /app
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

RUN chown -R www-data:www-data /app && \
    chmod -R 755 /app && \
    chmod -R 775 /app/var

USER www-data

EXPOSE 8000

CMD ["php-fpm"]
