import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';
import * as vueCompiler from 'vue/compiler-sfc';

export default defineConfig({
    ssr: {
        noExternal: ['laravel-vite-plugin'],
    },
    plugins: [
        vue({
            compiler: vueCompiler,
        }),
        laravel({
            input: [
                'resources/js/cp.js',
                'resources/css/cp.css',
            ],
            publicDirectory: 'resources/dist',
            buildDirectory: 'build',
            refresh: false,
        }),
    ],
});
