import {defineConfig} from 'vite'
import pluginLaravel from 'laravel-vite-plugin'
import pluginVue from '@vitejs/plugin-vue'
import path from 'path'
import pluginYaml from '@modyfi/vite-plugin-yaml'
import pluginEslint from 'vite-plugin-eslint'
import pluginCompression from 'vite-plugin-compression2'
import { viteStaticCopy as pluginStaticCopy } from 'vite-plugin-static-copy'
import {
  ViteImageOptimizer as imageOptimizerPlugin
} from 'vite-plugin-image-optimizer'

export default defineConfig({
  resolve: {
    alias: {
      '@images': path.resolve(__dirname, 'resources/assets/images'),
      '@resources': path.resolve(__dirname, 'resources'),
    },
  },
  plugins: [
    pluginEslint(),
    pluginStaticCopy({
      targets: [
        {
          src: path.resolve(__dirname, 'resources/assets/public/**/*'),
          dest: 'public',
        },
      ],
    }),
    pluginLaravel({
      input: [
        // JS
        'resources/js/app.js',

        // CSS
        'resources/css/app.css',
        'resources/css/mail.css',
      ],
      refresh: true,
    }),
    pluginVue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    pluginYaml(),
    imageOptimizerPlugin(),

    // Create gzip and brotli files on build
    pluginCompression({algorithm: 'gzip'}),
    pluginCompression({algorithm: 'brotliCompress'}),
  ],
})
