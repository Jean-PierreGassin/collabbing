import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'node:path';

const devServerPort = Number(process.env.VITE_DEV_SERVER_PORT ?? process.env.NODE_PORT ?? 5173);
const devServerHost = process.env.VITE_DEV_SERVER_HOST ?? '0.0.0.0';
const devServerOrigin = process.env.VITE_DEV_SERVER_ORIGIN ?? `http://127.0.0.1:${devServerPort}`;
const hmrHost = process.env.VITE_HMR_HOST ?? '127.0.0.1';
const appUrl = process.env.APP_URL ?? 'http://localhost:8080';

export default defineConfig({
    plugins: [
        vue(),
        tailwindcss(),
        laravel({
            input: ['resources/js/main.ts'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        host: devServerHost,
        port: devServerPort,
        origin: devServerOrigin,
        cors: {
            origin: [appUrl, /^http:\/\/localhost:8080$/, /^http:\/\/127\.0\.0\.1:8080$/],
        },
        hmr: {
            host: hmrHost,
        },
    },
});
