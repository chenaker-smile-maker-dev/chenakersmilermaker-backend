FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git libpng-dev libonig-dev libxml2-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd sockets \
 && pecl install redis && docker-php-ext-enable redis \
 && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock* ./

RUN composer install --no-dev --no-interaction --optimize-autoloader || true

# Copy application source
COPY . .

# Ensure correct permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 storage bootstrap/cache || true

EXPOSE 9000

CMD ["php-fpm"]
