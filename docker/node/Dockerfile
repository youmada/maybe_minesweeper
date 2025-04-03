FROM node:20

# 非rootユーザー「developer」を作成
RUN useradd -m developer

WORKDIR /var/www

# アプリケーションコードをコピー（ビルド時にコピーする場合）
COPY . .

# 依存関係のインストール
RUN npm install

# 非rootユーザーへ切り替え
USER developer

# ホットリロード用のコマンドを実行（Vite等を使用している前提）
CMD ["npm", "run", "dev"]