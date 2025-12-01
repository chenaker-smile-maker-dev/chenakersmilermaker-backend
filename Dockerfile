FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpng-dev libonig-dev libxml2-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd sockets \
 && pecl install redis && docker-php-ext-enable redis \
 && rm -rf /var/lib/apt/lists/*

# Install Node.js (for npm/vite build)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock* ./

# Install PHP composer dependencies BEFORE copying app
RUN composer install --no-dev --no-interaction --optimize-autoloader || composer install --no-dev --no-interaction

# Copy entire application
COPY . .

# Install npm dependencies and build assets
RUN npm ci || npm install || true
RUN npm run build || true

# Generate autoloader
RUN composer dump-autoload --optimize || true

# Ensure artisan is executable
RUN chmod +x artisan || true

# Ensure correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

CMD ["/usr/local/bin/entrypoint.sh"]
