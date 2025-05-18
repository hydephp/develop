import { Plugin } from 'vite';
export interface HydePluginOptions {
    /**
     * Asset entry points to process
     *
     * @default ['resources/assets/app.css', 'resources/assets/app.js']
     */
    input?: string[];
    /**
     * Enable hot reloading for content files
     *
     * @default true
     */
    refresh?: boolean;
    /**
     * Content directories to watch for changes
     *
     * @default ['_pages', '_posts', '_docs']
     */
    watch?: string[];
}
/**
 * HydePHP Vite plugin for realtime compiler integration
 */
export default function hydePlugin(options?: HydePluginOptions): Plugin;
