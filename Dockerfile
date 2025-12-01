FROM php:8.3-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpng-dev libonig-dev libxml2-dev libjpeg-dev libfreetype6-dev libicu-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-configure intl \
 && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd sockets intl \
 && pecl install redis && docker-php-ext-enable redis \
 && rm -rf /var/lib/apt/lists/*

# Install Node.js (for npm/vite build)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy entire application
COPY . .

# Install PHP dependencies first
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts 2>&1 || \
    composer install --no-dev --no-interaction 2>&1 || \
    echo "⚠️  Composer install attempted"

# Install npm dependencies and build assets
RUN npm ci 2>&1 || npm install 2>&1 || true
RUN npm run build 2>&1 || echo "⚠️  npm build attempted"
RUN ls -la public/build 2>&1 || echo "⚠️  public/build may not exist"

# Generate optimized autoloader
RUN composer dump-autoload --optimize 2>&1 || true

# Ensure artisan is executable
RUN chmod +x artisan || true

# Ensure correct permissions
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Copy entrypoint scripts
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/horizon-entrypoint.sh /usr/local/bin/horizon-entrypoint.sh
COPY docker/scheduler-entrypoint.sh /usr/local/bin/scheduler-entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/horizon-entrypoint.sh /usr/local/bin/scheduler-entrypoint.sh

EXPOSE 9000

CMD ["/usr/local/bin/entrypoint.sh"]
