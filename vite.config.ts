import { defineConfig, mergeConfig, UserConfig } from 'vite'
import { config } from './Framework/ViteConfig/v1/index'


/* export default mergeConfig(config, {
    build: {
        rollupOptions: {
            input: {
                ...config.build.rollupOptions.input,
                'index': 'src/index.ts',
            },
        },
    }
}); */
export default config