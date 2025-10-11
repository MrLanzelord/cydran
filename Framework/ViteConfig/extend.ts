import { mergeConfig, UserConfig } from 'vite'

export default function extendConfig(base: UserConfig, overrides: UserConfig): UserConfig {
    return mergeConfig(base, overrides)
}
