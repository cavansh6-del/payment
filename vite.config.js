import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/main.jsx'],
            refresh: true,
            publicDirectory: 'public_html',
        }),
        react(),
    ],
    build: {
        outDir: 'public_html',  // مسیر جدید خروجی
        manifest: true,               // فایل manifest.json را فعال کن
    },
    server: {
        host: 'http://135.125.113.94/',  // آدرس عمومی که دسترسی از هر شبکه را می‌دهد
      
        strictPort: false, // اطمینان از اینکه پورت 5173 در دسترس است
    },
});
