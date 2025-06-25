FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /app

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
