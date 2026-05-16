import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
    ],
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
