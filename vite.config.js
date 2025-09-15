import { defineConfig } from 'vite';
import { resolve } from 'node:path';

export default defineConfig(({ mode }) => ({
  root: process.cwd(),
  server: {
    port: 5173,
    strictPort: true,
    hmr: true,
    origin: 'http://localhost:5173',
  },
  build: {
    outDir: 'assets',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'src/js/app.js'),
        styles: resolve(__dirname, 'src/css/app.css')
      },
      output: {
        entryFileNames: 'js/[name]-[hash].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          const ext = assetInfo.name.split('.').pop();
          if (ext === 'css') return 'css/[name]-[hash][extname]';
          if (/(png|jpe?g|svg|gif|webp|avif)$/i.test(ext)) return 'images/[name]-[hash][extname]';
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
  },
}));
