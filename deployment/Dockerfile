# Use an official PHP 8.2 CLI image.
FROM php:8.3-cli

# Install common system packages and PHP extensions.
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libzip-dev zip \
 && docker-php-ext-install intl mbstring \
 && rm -rf /var/lib/apt/lists/*

# Copy the Composer binary from the official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory.
WORKDIR /app

# First copy only the files needed for dependencies.
COPY composer.json composer.lock* ./

# Install PHP dependencies.
RUN composer install --no-interaction --prefer-dist --no-scripts || true

# Copy the whole project
COPY . .

# Default command (can be overridden by docker compose)
CMD ["php", "--version"]
