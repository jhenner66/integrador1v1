FROM php:8.2-cli

# Instalar dependencias del sistema y PDO MySQL
RUN apt-get update && apt-get install -y libzip-dev unzip \
    && docker-php-ext-install pdo_mysql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar la carpeta de trabajo
WORKDIR /app
COPY . .

# Instalar los paquetes de Laravel
RUN composer install --no-dev --optimize-autoloader

# Iniciar el servidor
CMD php artisan serve --host=0.0.0.0 --port=$PORT