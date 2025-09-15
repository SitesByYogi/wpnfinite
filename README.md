# WPNfinite Starter Theme

Block-first, performance-minded theme with Vite bundling.

## Quick Start

1. Copy this folder into `wp-content/themes/wpnfinite`.
2. Activate **WPNfinite** in Appearance → Themes.
3. From the theme folder, install deps & run dev:
   ```bash
   npm i
   npm run dev
   ```
   Keep Vite running while you build; assets load from `http://localhost:5173`.
4. For production:
   ```bash
   npm run build
   ```
   This writes `/assets` and `manifest.json`. WordPress will enqueue the built files automatically.
