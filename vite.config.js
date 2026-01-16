import tailwindcss from "@tailwindcss/vite";
import dns from "dns";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

dns.setDefaultResultOrder("ipv4first");

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: "0.0.0.0",
        origin: "http://localhost:5173",
        cors: {
            origin: "*",
        },
        hmr: {
            host: "localhost",
        },
        watch: {
            usePolling: true,
        },
    },
});
