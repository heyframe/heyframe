import {defineConfig, loadEnv} from "vite";
import uni from "@dcloudio/vite-plugin-uni";
import * as path from "node:path";
import colors from 'picocolors';

export default defineConfig(({command, mode}) => {
  const isProd = command === 'build';
  const isDev = !isProd;
  const base = isProd ? '/bundles/uni/uni' : undefined;

  const {UNI_PLATFORM} = process.env
  console.log(colors.yellow(`UNI_PLATFORM: ${UNI_PLATFORM}`))

  const env = loadEnv(mode, path.resolve(process.cwd()))
  const {VITE_APP_PORT,} = env
  return {
    base,
    plugins: [
      uni(),
    ],
    resolve: {
      alias: {
        '@': path.join(process.cwd(), './src'),
        '@icon': path.join(process.cwd(), './src/static/icon'),
      },
    },
    server: {
      host: process.env.HOST ? process.env.HOST : 'localhost',
      port: Number(VITE_APP_PORT) || 9000,
    },
    build: {
      sourcemap: false,
      target: 'es6',
      minify: mode === 'development' ? false : 'esbuild',
    }
  }
});
