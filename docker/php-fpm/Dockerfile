FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpng-dev libonig-dev libxml2-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd sockets \
 && pecl install redis && docker-php-ext-enable redis \
 && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy entire application (including vendor if it exists)
COPY . .

# If vendor doesn't exist, install composer dependencies
RUN if [ ! -d "vendor" ]; then composer install --no-dev --no-interaction --optimize-autoloader 2>&1; fi

# Generate autoloader if needed
RUN composer dump-autoload --optimize 2>&1 || true

# Ensure artisan is executable
RUN chmod +x artisan 2>/dev/null || true

# Ensure correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 9000

CMD ["php-fpm"]
