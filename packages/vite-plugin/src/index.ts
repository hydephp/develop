import { Plugin, ResolvedConfig } from 'vite';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// Define the hot file path as a constant
const HOT_FILE_PATH = 'app/storage/framework/runtime/vite.hot';

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
 * Resolve the path to a resource
 * This function ensures that the path works when used as an ESM package as well
 */
function resolveResource(resource: string): string {
  // In ESM context, __dirname is not available, so we use fileURLToPath
  try {
    const __filename = fileURLToPath(import.meta.url);
    const __dirname = path.dirname(__filename);
    
    return path.resolve(__dirname, '../resources', resource);
  } catch (e) {
    // Fallback for CommonJS
    return path.resolve(__dirname, '../resources', resource);
  }
}

/**
 * Check if a file exists
 */
function fileExists(file: string): boolean {
  try {
    return fs.statSync(file).isFile();
  } catch {
    return false;
  }
}

/**
 * Check if the JavaScript file has actual content
 * This prevents empty app.js files from being compiled
 */
function hasJavaScriptContent(filePath: string): boolean {
  try {
    if (!fs.existsSync(filePath)) return false;
    const content = fs.readFileSync(filePath, 'utf-8');
    // Remove comments and check if there's any actual code
    return content.replace(/\/\*[\s\S]*?\*\/|\/\/.*/g, '').trim().length > 0;
  } catch (e) {
    return false;
  }
}

/**
 * HydePHP Vite plugin for realtime compiler integration
 */
export default function hydePlugin(options: HydePluginOptions = {}): Plugin {
  const {
    input = ['resources/assets/app.css', 'resources/assets/app.js'],
    refresh = true,
  } = options;

  let config: ResolvedConfig;
  let hotFilePath: string;

  return {
    name: 'hyde-vite-plugin',

    config(config, { command }) {
      // Only modify build configuration
      if (command === 'build') {
        // Process input files - only include app.js if it has content
        const resolvedInput = [];
        
        for (const entry of input) {
          const resolvedPath = path.resolve(process.cwd(), entry);
          
          // Only include app.js if it has actual content
          if (entry.endsWith('app.js')) {
            if (hasJavaScriptContent(resolvedPath) && fileExists(resolvedPath)) {
              resolvedInput.push(resolvedPath);
            }
          } else if (fileExists(resolvedPath)) {
            resolvedInput.push(resolvedPath);
          }
        }

        return {
          build: {
            outDir: '_media',
            emptyOutDir: false,
            rollupOptions: {
              input: resolvedInput,
              output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]'
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
          } catch (e) {
            // Ignore errors when removing hot file
          }
          process.exit();
        });
      });

      // Render the Vite index page when the root URL is requested
      server.middlewares.use((req, res, next) => {
        if (req.url === '/') {
          try {
            const indexPath = resolveResource('vite-index-page.html');
            const content = fs.readFileSync(indexPath, 'utf-8');
            res.end(content);
          } catch (e) {
            next();
          }
        } else {
          next();
        }
      });

      // Add additional watch paths for content files if refresh option is enabled
      if (refresh) {
        const contentDirs = ['_pages', '_posts', '_docs'];
        contentDirs.forEach(dir => {
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