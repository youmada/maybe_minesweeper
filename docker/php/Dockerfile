FROM php:8.3-fpm

# 必要なパッケージのインストール（例: pdo_mysql用ライブラリなど）
RUN apt-get update && apt-get install -y \
    # git \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libzip-dev 

RUN docker-php-ext-install pdo_mysql mbstring gd

RUN apt-get install netcat-openbsd -y

# Composer のインストール（公式イメージからコピー）
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 非rootユーザー「developer」を作成
RUN useradd -m developer

WORKDIR /var/www

# アプリケーションコードをコピー（ビルド時にコピーする場合）
COPY . .

# ファイルの所有権を developer に変更
RUN chown -R developer:developer /var/www


# ここで composer install を実行（※コードがコピー済みの場合）
RUN composer install

# エントリーポイントスクリプトをコピー
COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# 非rootユーザーへ切り替え
USER developer

# コンテナ起動時はエントリーポイント経由で php-fpm を起動
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]