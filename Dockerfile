FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    gd \
    zip \
    bcmath \
    intl \
    pcntl \
    sockets

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction && \
    npm ci && npm run build

# Remove node_modules to reduce image size
RUN rm -rf node_modules

# Create necessary directories
RUN mkdir -p storage/logs bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

# Set PHP-FPM user
RUN sed -i 's/user = www-data/user = www-data/' /usr/local/etc/php-fpm.conf && \
    sed -i 's/group = www-data/group = www-data/' /usr/local/etc/php-fpm.conf

EXPOSE 9000

CMD ["php-fpm"]
