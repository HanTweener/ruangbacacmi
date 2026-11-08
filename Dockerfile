FROM php:8.4-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure Apache DocumentRoot to Laravel public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files first for build caching
COPY composer.json composer.lock ./

# Install Composer dependencies (production only, no dev)
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

# Copy application source code
COPY . .

# Generate optimized autoload files
RUN composer dump-autoload --optimize --no-dev

# Copy OPcache configuration
COPY .docker/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

# Set permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Setup entrypoint script
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]

