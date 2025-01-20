import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/charts/chart-01.js',
                'resources/js/charts/chart-02.js',
                'resources/js/charts/chart-03.js',
                'resources/js/charts/chart-04.js',
            ],
            refresh: true,
        }),
        
    ],
    server: {
        cors: true,  // Enable CORS support
    },
    resolve: {
        alias: {
          $: path.resolve(__dirname, 'node_modules/jquery'),
        },
    },
});
