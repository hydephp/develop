import { Plugin, ResolvedConfig } from 'vite';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// The interprocess communication signal file for the web server
const HOT_FILE_PATH = 'app/storage/framework/runtime/vite.hot';

export interface HydePluginOptions {
  /**
   * Asset entry points to process
   * Supports glob patterns like 'resources/assets/*.js'
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
 * Resolve the path to a resource, ensuring that the path works when used as an ESM package.
 */
function resolveResource(resource: string): string {
  // In ESM context, __dirname is not available, so we use fileURLToPath
  try {
    const __filename = fileURLToPath(import.meta.url);
    const __dirname = path.dirname(__filename);

    return path.resolve(__dirname, '../resources', resource);
  } catch (error) {
    // Fallback for CommonJS
    return path.resolve(__dirname, '../resources', resource);
  }
}

/**
 * Check if a file exists and is a file
 */
function fileExists(file: string): boolean {
  try {
    return fs.statSync(file).isFile();
  } catch {
    return false;
  }
}

/**
 * Check if the JavaScript file has actual content to prevent empty app.js files from being compiled
 */
function hasJavaScriptContent(filePath: string): boolean {
  try {
    if (!fs.existsSync(filePath)) return false;
    const content = fs.readFileSync(filePath, 'utf-8');
    // Remove comments and check if there's any actual code
    return content.replace(/\/\*[\s\S]*?\*\/|\/\/.*/g, '').trim().length > 0;
  } catch (error) {
    return false;
  }
}

/**
 * Check if a string contains glob patterns
 */
function isGlobPattern(pattern: string): boolean {
  return pattern.includes('*') || pattern.includes('?') || pattern.includes('[');
}

/**
 * Expand a glob pattern to match files in the filesystem
 */
function expandGlobPattern(pattern: string, base = process.cwd()): string[] {
  // Normalize the pattern to use proper path separators
  const normalizedPattern = path.normalize(pattern);
  
  // Get the directory part before any glob syntax
  const parts = normalizedPattern.split(/[\\/]/);
  let globIndex = parts.findIndex(part => isGlobPattern(part));
  if (globIndex === -1) {
    // No glob pattern found
    return fileExists(path.resolve(base, normalizedPattern)) ? [normalizedPattern] : [];
  }
  
  // Build the base directory path (before the glob pattern)
  const baseDir = path.join(base, ...parts.slice(0, globIndex));
  
  // The remaining pattern parts (includes and after the glob)
  const remainingPattern = parts.slice(globIndex).join(path.sep);
  
  // Start with the baseDir
  let currentResults: string[] = [baseDir];
  let allResults: string[] = [];
  
  // Process each directory level that contains glob patterns
  for (let i = globIndex; i < parts.length; i++) {
    const nextResults: string[] = [];
    const part = parts[i];
    
    // For each current directory, get matching entries
    for (const dir of currentResults) {
      if (!fs.existsSync(dir)) continue;
      
      try {
        const entries = fs.readdirSync(dir, { withFileTypes: true });
        
        for (const entry of entries) {
          const fullPath = path.join(dir, entry.name);
          
          // Check if this entry matches the current pattern part
          if (matchGlobPattern(entry.name, part)) {
            if (i === parts.length - 1) {
              // Last part of the pattern
              if (entry.isFile()) {
                allResults.push(path.relative(base, fullPath));
              }
            } else if (entry.isDirectory()) {
              nextResults.push(fullPath);
            }
          }
        }
      } catch (error) {
        // Skip directories we can't read
      }
    }
    
    currentResults = nextResults;
  }
  
  return allResults;
}

/**
 * Match a filename against a simple glob pattern
 */
function matchGlobPattern(filename: string, pattern: string): boolean {
  // Convert glob pattern to regex
  const regexPattern = pattern
    .replace(/\./g, '\\.')  // Escape dots
    .replace(/\*/g, '.*')   // * matches any sequence
    .replace(/\?/g, '.')    // ? matches a single character
    .replace(/\[([^\]]+)\]/g, (_, chars) => `[${chars}]`); // character classes
  
  const regex = new RegExp(`^${regexPattern}$`);
  return regex.test(filename);
}

/**
 * Expand all glob patterns in an array of input paths
 */
function expandGlobPatterns(patterns: string[]): string[] {
  const results: string[] = [];
  
  for (const pattern of patterns) {
    if (isGlobPattern(pattern)) {
      results.push(...expandGlobPattern(pattern));
    } else {
      results.push(pattern);
    }
  }
  
  return results;
}

/**
 * HydePHP Vite plugin for realtime compiler integration
 */
export default function hydePlugin(options: HydePluginOptions = {}): Plugin {
  const {
    input = ['resources/assets/app.css', 'resources/assets/app.js'],
    watch = ['_pages', '_posts', '_docs'],
    refresh = true,
  } = options;

  let config: ResolvedConfig;
  let hotFilePath: string;

  return {
    name: 'hyde-vite-plugin',

    config(config, { command }) {
      // Only modify build configuration
      if (command === 'build') {
        // Process input files - use named keys to prevent filename collisions
        const resolvedInput: Record<string, string> = {};
        
        // Expand any glob patterns in the input array
        const expandedInput = expandGlobPatterns(input);
        
        for (const entry of expandedInput) {
          const resolvedPath = path.resolve(process.cwd(), entry);

          // Only include JS files if they have actual content
          if (entry.endsWith('.js')) {
            if (hasJavaScriptContent(resolvedPath) && fileExists(resolvedPath)) {
              // Use special key for app.js, otherwise use basename
              if (entry.endsWith('app.js')) {
                resolvedInput.js = resolvedPath;
              } else {
                const basename = path.basename(entry, path.extname(entry));
                resolvedInput[basename] = resolvedPath;
              }
            }
          } else if (entry.endsWith('.css')) {
            // For CSS files, include them if they exist
            if (fileExists(resolvedPath)) {
              // Use special key for app.css, otherwise use basename
              if (entry.endsWith('app.css')) {
                resolvedInput.css = resolvedPath;
              } else {
                const basename = path.basename(entry, path.extname(entry));
                resolvedInput[basename] = resolvedPath;
              }
            }
          } else if (fileExists(resolvedPath)) {
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
          } catch (error) {
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
          } catch (error) {
            next();
          }
        } else {
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