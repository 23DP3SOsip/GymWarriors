import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

const codespaceHost = process.env.CODESPACE_NAME
    ? `${process.env.CODESPACE_NAME}-5173.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}`
    : 'localhost';

const laravelOrigin = 'https://spooky-spooky-troll-x5rqxgw4rwjrh9p9q-8000.app.github.dev';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: {
            origin: laravelOrigin,
        },
        origin: `https://${codespaceHost}`,
        hmr: {
            host: codespaceHost,
            protocol: 'wss',
            clientPort: 443,
        },
    },
});