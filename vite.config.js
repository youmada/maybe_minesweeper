/// <reference types="vitest" />
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        port: 3000,
        strictPort: true,
        hmr: {
            host: "localhost", // ローカルホスト名を指定
        },
    },
    // plugins: [vue()],
    test: {
        globals: true, // グローバルに `describe`, `test` を使えるように
        environment: 'jsdom', // DOM操作をテストするための仮想ブラウザ
        // setupFiles: './vitest.setup.js', // 必要に応じてセットアップファイルを指定
    },
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
