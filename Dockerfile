# Sử dụng image PHP chính thức với phiên bản 8.x và Apache
FROM php:8.2-apache

# Cài đặt các extension PHP cần thiết
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install pdo_mysql zip

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cài đặt Node.js và npm
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy mã nguồn Laravel vào container
COPY . /var/www/html

# Thiết lập quyền truy cập cho các file và thư mục
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Cài đặt dependencies
RUN composer install --no-scripts --no-autoloader
RUN npm install && npm run build

# Tạo alias để dễ dàng chạy các lệnh Artisan
RUN echo "alias artisan='php artisan'" >> ~/.bashrc

# Mở cổng 80
EXPOSE 80

# Chạy lệnh này khi container khởi động
CMD ["apache2-foreground"]
