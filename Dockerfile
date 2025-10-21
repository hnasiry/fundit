# Build stage for Composer
FROM composer:2 as composer

# Final stage
FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libssl-dev \
    zip \
    unzip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Copy Composer from the Composer image
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Create necessary directories
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
    && mkdir -p /var/www/html/bootstrap/cache

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set the working directory and user
USER www-data

# Set the entrypoint script
ENTRYPOINT ["entrypoint.sh"]

# Start PHP-FPM
CMD ["php-fpm"]
