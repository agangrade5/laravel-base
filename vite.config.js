import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';
import path from 'path';

function copyBackendImages() {
    return {
        name: 'copy-backend-images',

        closeBundle() {
            const folders = [
                {
                    src: path.resolve(__dirname, 'resources/images/backend'),
                    dest: path.resolve(__dirname, 'public/assets/images/backend'),
                },
                {
                    src: path.resolve(__dirname, 'resources/images/flags'),
                    dest: path.resolve(__dirname, 'public/assets/images/flags'),
                },
            ];

            folders.forEach(({ src, dest }) => {
                if (fs.existsSync(src)) {
                    fs.mkdirSync(dest, { recursive: true });

                    fs.cpSync(src, dest, {
                        recursive: true,
                        force: true,
                    });

                    console.log('Images copied to:', dest);
                }
            });
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/backend/admin.css',
                'resources/js/backend/admin.js',
                'resources/js/backend/image-cropper.js',
                'resources/js/backend/change-password.js',
                'resources/js/backend/user.js',
                'resources/js/backend/activity-logs.js',
                'resources/js/backend/otp-settings.js',
                'resources/js/backend/twilio-settings.js',
                'resources/js/backend/email-settings.js',
                'resources/js/backend/aws-settings.js',
                'resources/js/backend/user-login.js',
                'resources/js/backend/verify-otp.js',
                'resources/js/backend/account-settings.js',
                'resources/js/backend/google2fa-settings.js',
                'resources/js/backend/general-settings.js',
                'resources/js/backend/country-code.js',
            ],
            buildDirectory: 'assets',
            refresh: true,
        }),
        tailwindcss(),
        copyBackendImages(),
    ],

    build: {
        outDir: 'public/assets',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            output: {
                entryFileNames: (chunkInfo) => {
                    if (chunkInfo.facadeModuleId && chunkInfo.facadeModuleId.endsWith('.css')) {
                        return 'js/backend/admin-style.js';
                    }
                    // chunkInfo.facadeModuleId is set for entry points and dynamic imports
                    return 'js/backend/[name].js';
                },
                chunkFileNames: 'js/backend/[name].js',
                assetFileNames: (assetInfo) => {
                    const name = assetInfo.name?.replace(/\\/g, '/') || '';

                    if (name.endsWith('.css')) {
                        return 'css/backend/[name][extname]';
                    }
                    if (/\.(woff2?|ttf|otf|eot)$/i.test(name)) {
                        return 'fonts/backend/[name][extname]';
                    }
                    return 'assets/backend/[name][extname]';
                },
            },
        },
    },
});
