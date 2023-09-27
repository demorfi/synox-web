import {resolve} from 'node:path';
import {defineConfig} from 'vite';
import vue from '@vitejs/plugin-vue';
import Components from 'unplugin-vue-components/vite';
import {BootstrapVueNextResolver} from 'unplugin-vue-components/resolvers';
import Icons from 'unplugin-icons/vite';
import IconsResolve from 'unplugin-icons/resolver';

export default defineConfig({
    publicDir: false,

    build: {
        chunkSizeWarningLimit: 1500,
        manifest             : false,
        outDir               : 'public',
        emptyOutDir          : false,
        rollupOptions        : {
            input : ['resource/assets/main.js'],
            output: {
                entryFileNames: 'assets/js/[name].js',
                chunkFileNames: 'assets/js/[name].js',
                assetFileNames: ({name}) => {
                    if (/\.(gif|jpe?g|png|svg)$/.test(name)) {
                        return 'assets/images/[name][extname]';
                    }
                    return 'assets/[ext]/[name].[ext]';
                }
            }
        }
    },

    resolve: {
        alias: {
            '@': resolve(__dirname, './resource/assets')
        }
    },

    plugins: [
        vue(),
        Components({
            resolvers: [BootstrapVueNextResolver(), IconsResolve({prefix: 'icon'})],
            dts      : true
        }),
        Icons({
            compiler   : 'vue3',
            autoInstall: true
        })
    ],

    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `
                    @import "@/sass/variables";
                `
            }
        }
    }
});