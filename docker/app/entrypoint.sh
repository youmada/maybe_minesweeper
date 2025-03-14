#!/bin/bash
set -e

# 必要に応じて待機処理（例：DBコンテナが起動するまで待つ）
while ! nc -z db 3306; do
  echo "Waiting for DB connection..."
  sleep 2
done

# 初回起動時のみ実行する処理
if [ ! -f "storage/oauth-private.key" ]; then
  echo "Running initial setup..."
  php artisan key:generate
  php artisan migrate --force
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
fi
# 引数をそのまま実行（CMD で指定された php-fpm など）
exec "$@"