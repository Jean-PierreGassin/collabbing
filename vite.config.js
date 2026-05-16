import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'node:path';

export default defineConfig({
    plugins: [
        vue(),
        tailwindcss(),
        laravel({
            input: ['resources/js/app.js', 'resources/js/main.ts'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        origin: 'http://localhost:5173',
        cors: {
            origin: [/^http:\/\/localhost:8080$/, /^http:\/\/127\.0\.0\.1:8080$/],
        },
        hmr: {
            host: 'localhost',
        },
    },
});
