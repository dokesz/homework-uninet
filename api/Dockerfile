FROM php:8.3.16-apache-bookworm

# Install system dependencies required for PHP extensions and Phalcon
RUN apt-get update && apt-get install -y \
    libpcre3-dev \
    libpq-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions that need to be compiled.
# # Note: fileinfo, json, and pdo are built into PHP by default.
RUN docker-php-ext-install pdo_pgsql mbstring

# Install and enable Phalcon
RUN pecl channel-update pecl.php.net
RUN pecl install phalcon
RUN docker-php-ext-enable phalcon

# Install Composer
# COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Phalcon IDE stubs (autocompletion and error checking)
RUN composer require phalcon/ide-stubs

# Enable mod rewrite to make htaccess work
RUN a2enmod rewrite
COPY api/apache2.conf /etc/apache2/apache2.conf

ENTRYPOINT [ "apache2-foreground" ]

WORKDIR /var/www/html
