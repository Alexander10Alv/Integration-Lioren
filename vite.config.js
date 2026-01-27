import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    server: {
        host: true,
        hmr: {
            host: "630824cc1694.ngrok-free.app",
            protocol: "wss",
        },
    },
});
