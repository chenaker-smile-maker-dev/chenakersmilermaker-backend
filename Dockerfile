FROM ubuntu:24.04

# Install dependencies required for the installer script and Laravel
ENV TERM=xterm
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    ca-certificates \
    procps \
    && rm -rf /var/lib/apt/lists/*
    
# Install PHP, Composer, and Laravel via the provided script
RUN /bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.4)"

# Add the binaries to PATH
ENV PATH="/root/.config/herd-lite/bin:$PATH"
ENV PHP_INI_SCAN_DIR="/root/.config/herd-lite/bin:$PHP_INI_SCAN_DIR"

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Setup .env file
RUN cp .env.example .env

# Setup environment variables
ENV APP_ENV=production
ENV APP_DEBUG=true
ENV APP_URL=https://chenakersmilermaker.onrender.com
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/var/www/html/database/database.sqlite
ENV QUEUE_CONNECTION=sync
ENV CACHE_STORE=database
ENV SESSION_DRIVER=database
ENV LOG_CHANNEL=stderr

# Install PHP dependencies
RUN composer install --optimize-autoloader

# Setup SQLite database and permissions
RUN mkdir -p database && touch database/database.sqlite && \
    chown -R root:root database storage bootstrap/cache && \
    chmod -R 777 database storage bootstrap/cache

# Expose port (Render sets PORT env)
EXPOSE 8000

# Start server
CMD sh -c "php artisan key:generate --force && php artisan storage:link && php artisan migrate --force && php artisan db:seed --force && php artisan optimize && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"
