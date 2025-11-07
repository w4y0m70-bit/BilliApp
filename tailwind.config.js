import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // ベースカラー（アプリ全体で共通）
                base: {
                    DEFAULT: '#10B981',  // 緑
                    dark: '#059669',
                    light: '#6EE7B7',
                },

                // ユーザー専用カラー
                user: {
                    DEFAULT: '#1E40AF', // 青系
                    dark: '#1E3A8A',    // ちょっと濃い青
                    light: '#3B82F6',   // 明るい青

                },

                // 管理者専用カラー
                admin: {
                    DEFAULT: '#F5650B',  // オレンジ
                    dark: '#C0500E',
                    light: '#F08030',
                },
            },
        },
    },
    plugins: [forms],
};
