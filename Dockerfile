# Sử dụng PHP 8.4 làm base image
FROM php:8.4-cli

# Cài đặt các thư viện cần thiết cho PHP và các tiện ích
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Cài đặt Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Cài đặt Xdebug và cấu hình để bật code coverage
RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name 'xdebug.so' | head -n 1)" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Cài đặt các phụ thuộc Laravel và PestPHP
WORKDIR /var/www
COPY . /var/www
RUN composer install
RUN composer require pestphp/pest --dev

# Expose port để PHP server chạy
EXPOSE 9000

# Command để chạy PHP built-in server
CMD ["php", "-S", "0.0.0.0:9000", "-t", "public"]
