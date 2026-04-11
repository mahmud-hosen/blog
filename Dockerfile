# Dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    npm \
    nodejs

# Install PHP extensions including sockets
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd sockets

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer 

# Set working directory
WORKDIR /var/www

# প্রথম . (source) and দ্বিতীয় . (destination) , local project-এর সব file → container-এর WORKDIR-এ কপি করো  so COPY (local folder) → /var/www
COPY . .   

RUN composer install

# Fix permissions for Laravel storage and cache folders
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache




EXPOSE 9000
CMD ["php-fpm"]