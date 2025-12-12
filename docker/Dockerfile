FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev libicu-dev  libpng-dev libzip-dev  libjpeg-dev  \
    libfreetype6-dev libonig-dev build-essential \
    locales jpegoptim optipng pngquant gifsicle \
    vim zip unzip git libldap2-dev procps \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring zip exif pcntl ldap \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

#Supervisor
COPY docker/start.sh /usr/local/bin/start

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install opcache

# Copy the application
COPY . /var/www
