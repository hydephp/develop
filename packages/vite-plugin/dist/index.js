import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
// The interprocess communication signal file for the web server
const HOT_FILE_PATH = 'app/storage/framework/runtime/vite.hot';
/**
 * Resolve the path to a resource, ensuring that the path works when used as an ESM package.
 */
function resolveResource(resource) {
    // In ESM context, __dirname is not available, so we use fileURLToPath
    try {
        const __filename = fileURLToPath(import.meta.url);
        const __dirname = path.dirname(__filename);
        return path.resolve(__dirname, '../resources', resource);
    }
    catch (error) {
        // Fallback for CommonJS
        return path.resolve(__dirname, '../resources', resource);
    }
}
/**
 * Check if a file exists and is a file
 */
function fileExists(file) {
    try {
        return fs.statSync(file).isFile();
    }
    catch {
        return false;
    }
}
/**
 * Check if the JavaScript file has actual content to prevent empty app.js files from being compiled
 */
function hasJavaScriptContent(filePath) {
    try {
        if (!fs.existsSync(filePath))
            return false;
        const content = fs.readFileSync(filePath, 'utf-8');
        // Remove comments and check if there's any actual code
        return content.replace(/\/\*[\s\S]*?\*\/|\/\/.*/g, '').trim().length > 0;
    }
    catch (error) {
        return false;
    }
}
/**
 * HydePHP Vite plugin for realtime compiler integration
 */
export default function hydePlugin(options = {}) {
    const { input = ['resources/assets/app.css', 'resources/assets/app.js'], watch = ['_pages', '_posts', '_docs'], refresh = true, } = options;
    let config;
    let hotFilePath;
    return {
        name: 'hyde-vite-plugin',
        config(config, { command }) {
            // Only modify build configuration
            if (command === 'build') {
                // Process input files - use named keys to prevent filename collisions
                const resolvedInput = {};
                for (const entry of input) {
                    const resolvedPath = path.resolve(process.cwd(), entry);
                    // Only include app.js if it has actual content
                    if (entry.endsWith('app.js')) {
                        if (hasJavaScriptContent(resolvedPath) && fileExists(resolvedPath)) {
                            resolvedInput.js = resolvedPath;
                        }
                    }
                    else if (entry.endsWith('app.css') && fileExists(resolvedPath)) {
                        resolvedInput.css = resolvedPath;
                    }
                    else if (fileExists(resolvedPath)) {
                        const basename = path.basename(entry, path.extname(entry));
                        resolvedInput[basename] = resolvedPath;
                    }
                }
                return {
                    build: {
                        outDir: '_media',
                        emptyOutDir: false,
                        rollupOptions: {
                            input: resolvedInput,
                            output: {
                                entryFileNames: (chunkInfo) => {
                                    // Use app.js for the JS entry point 
                                    if (chunkInfo.name === 'js') {
                                        return 'app.js';
                                    }
                                    return '[name].js';
                                },
                                chunkFileNames: '[name].js',
                                assetFileNames: (assetInfo) => {
                                    // Use app.css for CSS assets
                                    if (assetInfo.name && assetInfo.name.endsWith('.css')) {
                                        return 'app.css';
                                    }
                                    return '[name].[ext]';
                                }
                            }
                        }
                    }
                };
            }
        },
        configResolved(resolvedConfig) {
            config = resolvedConfig;
            hotFilePath = path.resolve(process.cwd(), HOT_FILE_PATH);
        },
        configureServer(server) {
            // Create hot file when Vite server starts
            fs.mkdirSync(path.dirname(hotFilePath), { recursive: true });
            fs.writeFileSync(hotFilePath, '');
            // Remove hot file when Vite server closes
            ['SIGINT', 'SIGTERM'].forEach(signal => {
                process.on(signal, () => {
                    try {
                        fs.rmSync(hotFilePath);
                    }
                    catch (error) {
                        // Ignore errors when removing hot file
                    }
                    process.exit();
                });
            });
            // Render the Vite index page when the root URL is requested
            server.middlewares.use((req, res, next) => {
                if (req.url === '/') {
                    try {
                        const indexPath = resolveResource('index.html');
                        const content = fs.readFileSync(indexPath, 'utf-8');
                        res.end(content);
                    }
                    catch (error) {
                        next();
                    }
                }
                else {
                    next();
                }
            });
            // Add additional watch paths for content files if refresh option is enabled
            if (refresh) {
                watch.forEach(dir => {
                    const contentPath = path.resolve(process.cwd(), dir);
                    if (fs.existsSync(contentPath)) {
                        server.watcher.add(path.join(contentPath, '**'));
                        server.watcher.add(path.join(contentPath, '**/**'));
                    }
                });
            }
        }
    };
}
