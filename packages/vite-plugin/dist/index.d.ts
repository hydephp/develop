import { Plugin } from 'vite';
export interface HydePluginOptions {
    /**
     * Asset entry points to process
     * @default ['resources/assets/app.css', 'resources/assets/app.js']
     */
    input?: string[];
    /**
     * Enable hot reloading for content files
     * @default true
     */
    refresh?: boolean;
}
/**
 * HydePHP Vite plugin for realtime compiler integration
 */
export default function hydePlugin(options?: HydePluginOptions): Plugin;
