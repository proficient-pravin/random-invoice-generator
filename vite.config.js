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
                'resources/js/charts/map-01.js'
            ],
            refresh: true,
        }),
        
    ],
    resolve: {
        alias: {
          $: path.resolve(__dirname, 'node_modules/jquery'),
        },
    },
});
