FROM php:8.3-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    sqlite3 libsqlite3-dev \
    libzip-dev zip unzip curl git \
    && docker-php-ext-install pdo pdo_sqlite zip \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first for better Docker layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install  --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Copy environment file (create .env.production with your settings)
ARG ENV_FILE=.env
COPY ${ENV_FILE} .env

# Create Laravel required directories
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache database

# Set proper ownership and permissions BEFORE switching user
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 775 storage bootstrap/cache database

# Run final Composer scripts and optimizations
RUN composer dump-autoload --optimize \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Set final permissions for files
RUN find storage -type f -exec chmod 664 {} \; \
    && find storage -type d -exec chmod 775 {} \;

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD php artisan inspire || exit 1

# Run PHP-FPM
CMD ["php-fpm"]
