import { defineConfig } from 'vite'
import legacy from '@vitejs/plugin-legacy'
import path from 'path'
import fs from 'fs'

const distRoot = path.resolve(__dirname, 'public/dist')
const externalAssetsRoot = path.resolve(__dirname, 'public/assets')
const themeAssetsRoot = path.resolve(__dirname, 'src')

// Detecta todos los .ts y .js dentro de src/
function getThemeScripts() {
    const entries = {}

    function walk(dir) {
        fs.readdirSync(dir).forEach(file => {
            const fullPath = path.join(dir, file)
            const relPath = path.relative(themeAssetsRoot, fullPath).replace(/\\/g, '/')
            if (fs.statSync(fullPath).isDirectory()) {
                walk(fullPath)
            } else if (/\.(ts|js)$/.test(file)) {
                entries[`theme/${relPath}`] = fullPath
            }
        })
    }

    walk(themeAssetsRoot)
    return entries
}

// Detecta todos los .css dentro de src/
function getThemeStyles() {
    const entries = {}

    function walk(dir) {
        fs.readdirSync(dir).forEach(file => {
            const fullPath = path.join(dir, file)
            const relPath = path.relative(themeAssetsRoot, fullPath).replace(/\\/g, '/')
            if (fs.statSync(fullPath).isDirectory()) {
                walk(fullPath)
            } else if (/\.css$/.test(file)) {
                entries[`theme/${relPath}`] = fullPath
            }
        })
    }

    walk(themeAssetsRoot)
    return entries
}

// Detecta todos los .js y .css dentro de public/assets/
function getExternalAssets() {
    const entries = {}

    function walk(dir) {
        fs.readdirSync(dir).forEach(file => {
            const fullPath = path.join(dir, file)
            const relPath = path.relative(externalAssetsRoot, fullPath).replace(/\\/g, '/')
            if (fs.statSync(fullPath).isDirectory()) {
                walk(fullPath)
            } else if (/\.(js|css)$/.test(file)) {
                entries[`assets/${relPath}`] = fullPath
            }
        })
    }

    walk(externalAssetsRoot)
    return entries
}

export default defineConfig({
    root: themeAssetsRoot, // punto de entrada para Vite
    base: '/wp-content/themes/cydran-theme/public/dist/',
    plugins: [
        legacy({
            targets: ['defaults', 'not IE 11'],
            additionalLegacyPolyfills: ['regenerator-runtime/runtime'],
            renderLegacyChunks: false
        })
    ],
    build: {
        outDir: distRoot,
        emptyOutDir: true,
        manifest: true,
        modulePreload: {
            polyfill: false
        },
        cssCodeSplit: true,
        rollupOptions: {
            input: {
                ...getThemeScripts(),
                ...getThemeStyles(),
                ...getExternalAssets()
            },
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return 'vendor'
                    }
                },
                entryFileNames: ({ name }) =>
                    name.endsWith('.css')
                        ? '[name].[hash].css'
                        : '[name].[hash].js',
                chunkFileNames: 'chunks/[name].[hash].js',
                assetFileNames: ({ name }) => {
                    if (!name) return 'assets/[name].[hash].[ext]'
                    const rel = name.replace(/^.*(assets|theme)\//, '')
                    return `${name.startsWith('theme/') ? 'theme' : 'assets'}/${rel.replace(/\.[^/.]+$/, '')}.[hash].[ext]`
                }
            }
        }
    }
})
