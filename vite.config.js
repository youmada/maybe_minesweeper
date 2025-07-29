/// <reference types="vitest" />
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    server: {
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'maybe_minesweeper.test', // ローカルホスト名を指定
        },
        cors: {
            origin: 'https://maybe_minesweeper.test',
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
