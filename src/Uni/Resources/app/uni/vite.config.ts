import {defineConfig, loadEnv} from "vite";
import uni from "@dcloudio/vite-plugin-uni";
import * as path from "node:path";

export default ({command, mode}) => {

  const env = loadEnv(mode, path.resolve(process.cwd()))
  return defineConfig({
    plugins: [
      uni(),
    ],
    resolve: {
      alias: {
        '@': path.join(process.cwd(), './src'),
      },
    },
    build: {
      sourcemap: false,
      target: 'es6',
      minify: mode === 'development' ? false : 'esbuild',
    },
  });
}
