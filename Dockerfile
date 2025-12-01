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
    npm \
    autoconf \
    make \
    g++ \
    netcat-openbsd

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
    exif

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer inline
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts 2>&1

# Install Node dependencies and build assets
RUN npm install 2>&1 && npm run build 2>&1

# Cache config (will use environment variables at runtime)
RUN mkdir -p storage/logs bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Set PHP-FPM user
RUN sed -i 's/user = www-data/user = www-data/' /usr/local/etc/php-fpm.conf && \
    sed -i 's/group = www-data/group = www-data/' /usr/local/etc/php-fpm.conf

# Copy and setup entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]
