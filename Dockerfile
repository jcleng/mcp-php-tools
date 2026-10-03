FROM alpine:latest

WORKDIR /app

# 基础工具与 CA 证书（HTTPS 下载、Composer 需要）
RUN apk add --no-cache ca-certificates curl wget

# 安装静态编译的 PHP 8.4.1 CLI 二进制（单文件，含常用扩展）
RUN curl -fsSL https://dl.static-php.dev/static-php-cli/common/php-8.4.1-cli-linux-x86_64.tar.gz | tar -xz -C /usr/local/bin \
    && chmod +x /usr/local/bin/php \
    && php -v

# 安装 Composer（纯 PHP phar）
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 先安装依赖，利用层缓存
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts

# 复制项目源码
COPY . .

EXPOSE 8087

CMD ["sh", "-c", "PHP_CLI_SERVER_WORKERS=20 php -S 0.0.0.0:8087 router.php"]
