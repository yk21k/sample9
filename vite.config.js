import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

console.log(laravel); // Check what is being imported

export default defineConfig({
  plugins: [
    laravel.default(), // Access the 'default' export and call the function
  ],
});

// Export the Vite config using ESM syntax
// export default defineConfig({
//   plugins: [laravel()],
// });

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: [
//                 'resources/sass/app.scss',
//                 'resources/sass/product-detail.scss',
//                 'resources/js/app.js',
//             ],
//             refresh: true,
//         }),
//     ],

// });
