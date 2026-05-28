FROM php:8.3-cli

# Cai dat cac thu vien he thong can thiet
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    && docker-php-ext-install \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        gd \
        mbstring \
        opcache \
        intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cai Composer tu image chinh thuc
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy source code
COPY . .

# Cai PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Tao thu muc writable
RUN mkdir -p writable/logs writable/cache writable/session writable/uploads \
    && chmod -R 777 writable/

EXPOSE 8080

CMD ["bash", "railway-start.sh"]
