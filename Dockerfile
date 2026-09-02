FROM registry.cn-hangzhou.aliyuncs.com/jcleng/gitbuild-php:8.1-cli

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist

COPY . .

EXPOSE 8087

CMD ["sh", "-c", "PHP_CLI_SERVER_WORKERS=20 php -S 0.0.0.0:8087 router.php"]
