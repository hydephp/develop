# HydeFront v4 Development Plan

## Abstract

We are updating how HydeFront is handled for v2. Instead of declaring styles directly in HydeFront alongside Tailwind, we will refactor those styles into Tailwind. 

HydeFront will serve two main purposes:  

1. It will continue to include the precompiled `app.css` file bundled with new apps, available through the HydeFront CDN.  
2. It will act as a component library, allowing users to include granular styles from `app.css`, which we will preconfigure.  

For example, users can include our Tailwind styles granularly using just the `app.css` file from the HydeFront package. This file will be compiled alongside Tailwind. If users prefer customization, they can remove the import and add the specific styles they want.  

## Refactoring Plan

### 1. Restructure HydeFront Package
- Create a new directory structure in HydeFront:
  ```
  hydefront/
  ├── dist/
  │   └── app.css       # Pre-compiled styles for CDN
  ├── components/       # New component-based structure
  │   ├── base/        # Base styles
  │   ├── docs/        # Documentation styles
  │   ├── markdown/    # Markdown styles
  │   └── utilities/   # Utility styles
  └── package.json
  ```

### 2. Convert SCSS to Tailwind Components
- Convert existing SCSS styles from `hyde.scss` (reference: `packages/hydefront/hyde.scss`) into Tailwind components
- Create separate files for each component category that can be imported individually
- Example component structure:
  ```javascript
  // components/docs.js
  module.exports = {
    '.docs-sidebar': {
      '@apply ...': {},
    }
  }
  ```

### 3. Update Build Process
- Modify the Vite configuration (reference: `vite.config.js`) to handle component-based builds
- Update the build scripts in package.json to:
  - Build individual components
  - Generate the complete app.css for CDN distribution
  - Add new script for component-based builds

### 4. Update Framework Integration
Key files to modify:

```blade
// packages/framework/resources/views/layouts/styles.blade.php
{{-- Prevent Alpine.js flashes --}}
<style>[x-cloak] {display: none!important}</style>

{{-- The compiled Tailwind/App styles --}}
@if(Vite::running())
    {{ Vite::assets(['resources/assets/app.css']) }}
@else
    @if(config('hyde.load_app_styles_from_cdn', false))
        <link rel="stylesheet" href="{{ HydeFront::cdnLink('app.css') }}">
    @elseif(Asset::exists('app.css'))
        <link rel="stylesheet" href="{{ Asset::get('app.css') }}">
    @endif


    {{-- Dynamic TailwindCSS Play CDN --}}
    @if(config('hyde.use_play_cdn', false))
        <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
        <script>tailwind.config = { {!! HydeFront::injectTailwindConfig() !!} }</script>
        <script>console.warn('The HydePHP TailwindCSS Play CDN is enabled. This is for development purposes only and should not be used in production.', 'See https://hydephp.com/docs/1.x/managing-assets');</script>
    @endif
@endif

{{-- Add any extra styles to include after the others --}}
@stack('styles')
```


### 5. Documentation Updates
- Update the asset management documentation
- Create new documentation for component-based usage
- Update the HydeFront README (reference: `packages/hydefront/README.md`)

### 6. Migration Path
1. Create a new major version branch
2. Keep existing functionality working while adding new component system
3. Provide migration guide for users moving from v1 to v2
4. Update the Hyde starter template to use new component system

### 7. Configuration Updates
- Update the Hyde configuration (reference: `config/hyde.php`) to support component-based imports
- Add new configuration options for granular style inclusion

### 8. Testing Strategy
1. Create tests for individual components
2. Test CDN distribution
3. Test backward compatibility layer
4. Test integration with Hyde framework

### 9. Implementation Phases
1. **Phase 1**: Create new component structure
2. **Phase 2**: Convert existing styles
3. **Phase 3**: Update build system
4. **Phase 4**: Update framework integration
5. **Phase 5**: Documentation and migration guide
6. **Phase 6**: Testing and refinement

### 10. Breaking Changes to Document
- New import syntax for granular components
- Changes to configuration structure
- Updates to asset compilation process
- CDN usage changes

This plan maintains backward compatibility through the CDN while providing a more flexible component-based approach for customization. The changes align with modern frontend practices while keeping Hyde's simplicity.
