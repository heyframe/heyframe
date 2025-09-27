import Uni from '@uni-helper/plugin-uni'
import UniHelperComponents from '@uni-helper/vite-plugin-uni-components'
import UniHelperLayouts from '@uni-helper/vite-plugin-uni-layouts'
import UniHelperManifest from '@uni-helper/vite-plugin-uni-manifest'
import UniHelperPages from '@uni-helper/vite-plugin-uni-pages'
import UnoCSS from 'unocss/vite'
import AutoImport from 'unplugin-auto-import/vite'
import {defineConfig, loadEnv} from 'vite'
import UniPolyfill from 'vite-plugin-uni-polyfill'
import {WotResolver} from "@uni-helper/vite-plugin-uni-components/resolvers";
import * as path from "node:path";

export default defineConfig(({command, mode}) => {
  const isProd = command === 'build';
  const isDev = !isProd;

  const env = loadEnv(mode, path.resolve(process.cwd()));
  const {VITE_APP_PORT,UNI_PLUGIN_NAME} = env;

  const base = isProd ? `/bundles/${UNI_PLUGIN_NAME}/uni` : undefined;

  return {
    base,
    resolve: {
      alias: {
        '@': path.resolve('./src'),
        '@static': path.resolve('./src/static')
      }
    },
    plugins: [
      UniHelperManifest(),
      UniHelperPages({
        minify:true,
        exclude: ['**/components/**/**.*'],
        dts: 'src/uni-pages.d.ts',
      }),
      UniHelperLayouts(),
      UniHelperComponents({
        resolvers: [WotResolver()],
        dts: 'src/components.d.ts',
        directoryAsNamespace: true,
      }),
      Uni(),
      UniPolyfill(),
      AutoImport({
        imports: ['vue', '@vueuse/core', 'uni-app'],
        dts: 'src/auto-imports.d.ts',
        dirs: ['src/app/composables', 'src/app/stores'],
        vueTemplate: true,
      }),
      UnoCSS(),
    ],
    server: {
      hmr: true,
      host: process.env.HOST ? process.env.HOST : 'localhost',
      port: Number(VITE_APP_PORT) || 9000,
      proxy: isDev ? {
        '/front-api': {
          target: process.env.APP_URL,
          changeOrigin: true,
          secure: false,
        },
      } : undefined,
    },
  }
});
