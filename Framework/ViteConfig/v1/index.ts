/**
 * @license MIT
 * Copyright (c) 2025 Juan / Cydran Framework
 * Permission is hereby granted, free of charge, to any person obtaining a copy...
 */

import { defineConfig, loadEnv } from 'vite'
import chokidar from 'chokidar'
import legacy from '@vitejs/plugin-legacy'
import path from 'path'
import fs from 'fs'
import chalk from 'chalk'

const dirname = __dirname + '/../../../'
const distRoot = path.resolve(dirname, 'public/dist')
const externalAssetsRoot = path.resolve(dirname, 'public/assets')
const themeAssetsRoot = path.resolve(dirname, 'src')

function getThemeScripts() {
    const entries: Record<string, string> = {}
    function walk(dir: string) {
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

function getThemeStyles() {
    const entries: Record<string, string> = {}
    function walk(dir: string) {
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

function getExternalAssets() {
    const entries: Record<string, string> = {}
    function walk(dir: string) {
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

export const config = defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '')
    const useHttps = env.VITE_USE_HTTPS === 'true'
    const devPort = parseInt(env.VITE_DEV_PORT || '5173', 10)
    const devHost = env.VITE_DEV_HOST || `${useHttps ? 'https' : 'http'}://localhost:${devPort}`

    const keyPath = path.resolve(__dirname, 'certs/localhost-key.pem')
    const certPath = path.resolve(__dirname, 'certs/localhost-cert.pem')

    const httpsConfig = useHttps && fs.existsSync(keyPath) && fs.existsSync(certPath)
        ? {
            https: {
                key: fs.readFileSync(keyPath),
                cert: fs.readFileSync(certPath),
            },
            hmr: {
                protocol: 'wss',
                host: 'localhost',
                port: devPort,
            }
        }
        : {
            hmr: {
                protocol: 'ws',
                host: 'localhost',
                port: devPort,
            }
        }

    if (useHttps && !httpsConfig.https) {
        console.warn('[vite] ⚠️ VITE_USE_HTTPS está activado pero no se encontraron certificados. Se usará HTTP.')
    }

    if (mode === 'development' && !env.VITE_DEV_HOST) {
        console.warn('[vite] ⚠️ VITE_DEV_HOST no está definido en .env')
    }

    return {
        root: themeAssetsRoot,
        base: mode === 'development'
            ? '/'
            : '/wp-content/themes/cydran/public/dist/',
        plugins: [
            legacy({
                targets: ['defaults', 'not IE 11'],
                additionalLegacyPolyfills: ['regenerator-runtime/runtime'],
                renderLegacyChunks: false
            }),
            {
                name: 'watch-ui-twig-and-php',
                configureServer(server) {
                    const root = path.resolve(__dirname, '../../../')
                    const watchDirs = [
                        path.resolve(root, 'src'),
                        path.resolve(root, 'Framework'),
                    ]

                    const editCounts = new Map<string, { count: number; lastEdit: number }>()

                    const watcher = chokidar.watch(watchDirs, {
                        ignored: /node_modules/,
                        ignoreInitial: true,
                        persistent: true,
                    })

                    watcher.on('change', (file) => {
                        if (!/\.(php|twig|ui\.twig)$/.test(file)) return

                        const now = Date.now()
                        const relPath = path.relative(themeAssetsRoot, file).replace(/\\/g, '/')
                        const displayPath = relPath.startsWith('..') ? path.basename(file) : `/${relPath}`

                        const entry = editCounts.get(file)
                        if (entry && now - entry.lastEdit < 2000) {
                            entry.count += 1
                            entry.lastEdit = now
                        } else {
                            editCounts.set(file, { count: 1, lastEdit: now })
                        }

                        const count = editCounts.get(file)?.count || 1
                        const suffix = chalk.gray(`(x${count})`)
                        const time = chalk.dim(new Date().toLocaleTimeString('en-GB', { hour12: false }))
                        const label = chalk.cyan('[vite]')
                        const action = chalk.yellow('hmr update')
                        const pathColor = chalk.green(displayPath)

                        console.log(`${time} ${label} ${action} ${pathColor} ${suffix}`)
                        server.ws.send({ type: 'full-reload' })
                    })
                }
            }
        ],
        server: {
            ...httpsConfig,
            port: devPort,
            origin: devHost,
            strictPort: true,
            cors: true,
            headers: {
                'Access-Control-Allow-Origin': '*',
            }
        },
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
    }
})
