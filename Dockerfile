FROM php:8.2-fpm

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    git \
    nginx \
    supervisor \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    xml \
    curl \
    bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install and build frontend assets
RUN npm install && npm run build

# Create necessary directories
RUN mkdir -p storage/logs storage/framework/views storage/framework/cache \
    && chmod -R 777 storage bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Copy nginx config
RUN mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled
COPY --chown=www-data:www-data nginx.conf /etc/nginx/nginx.conf

# Copy supervisor config
RUN mkdir -p /etc/supervisor/conf.d
COPY supervisord.conf /etc/supervisor/supervisord.conf

# Expose port
EXPOSE 8000

# Start nginx and PHP-FPM
CMD ["/bin/bash", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
