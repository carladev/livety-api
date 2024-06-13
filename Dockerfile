# Usa la imagen oficial de PHP 8 con CLI
FROM php:8-cli

# Instala las extensiones de PHP necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    && docker-php-ext-install pdo pdo_mysql

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia el código del proyecto al contenedor
COPY . /var/www/html

# Establecer variable de entorno para permitir plugins de Composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Instala dependencias de Composer
RUN composer install

# Establece los permisos necesarios
RUN chown -R www-data:www-data /var/www/html

# Exponemos el puerto 8888
EXPOSE 8888

# Inicia el servidor PHP embebido
CMD ["php", "-S", "0.0.0.0:8888", "-t", "public"]
