# HydeFront v4 Development Plan

## Abstract

We are updating how HydeFront is handled for v2. Instead of declaring styles directly in HydeFront alongside Tailwind, we will refactor those styles into Tailwind. 

HydeFront will serve two main purposes:  

1. It will continue to include the precompiled `app.css` file bundled with new apps, available through the HydeFront CDN.  
2. It will act as a component library, allowing users to include granular styles from `app.css`, which we will preconfigure.  

For example, users can include our Tailwind styles granularly using just the `app.css` file from the HydeFront package. This file will be compiled alongside Tailwind. If users prefer customization, they can remove the import and add the specific styles they want.  

## Goals

Based on the codebase, here's a detailed plan to refactor HydeFront:

### Phase 1: Setup
- [ ] Create new branch `feature/hydefront-v4`
- [ ] Update HydeFront version to 4.0.0-dev in package.json
- [ ] Create new directory structure in HydeFront:
```
src/
  components/
  presets/
  app.css
```

### Phase 2: Component Migration
- [ ] Audit current styles in hyde.scss (reference: `packages/hydefront/hyde.scss`)
- [ ] Create individual Tailwind component files for:
  - [ ] Documentation styles
  - [ ] Search functionality
  - [ ] Sidebar components
  - [ ] Markdown typography
  - [ ] Code blocks
  - [ ] Blockquotes
- [ ] Move Alpine.js utilities to separate component file

### Phase 3: Preset Configuration
- [ ] Create base preset that includes all current functionality
- [ ] Create minimal preset with only core styles
- [ ] Create documentation preset focused on docs-specific styles
- [ ] Update the app.css compilation process to use presets

### Phase 4: Build System Updates
- [ ] Update Vite config to handle new component structure
- [ ] Modify build scripts to:
  - [ ] Compile individual components
  - [ ] Generate preset bundles
  - [ ] Create the main app.css bundle
- [ ] Update the CDN distribution process

### Phase 5: Framework Integration
- [ ] Update Hyde Framework to support new HydeFront structure
- [ ] Modify Asset facade to handle granular component loading
- [ ] Update default Hyde project scaffold
- [ ] Create migration guide for existing projects

### Phase 6: Documentation
- [ ] Document new component system
- [ ] Create examples for common customization scenarios
- [ ] Update HydeFront README
- [ ] Add migration guide to Hyde docs

### Phase 7: Testing & Release
- [ ] Create test suite for components
- [ ] Test backwards compatibility
- [ ] Create release candidate
- [ ] Update CDN infrastructure
- [ ] Release v4.0.0

### Phase 8: Cleanup
- [ ] Remove deprecated files and methods
- [ ] Update all references to old HydeFront structure
- [ ] Archive v3 documentation
- [ ] Update GitHub workflows

This plan maintains backwards compatibility while introducing the new component system. The main app.css will still be available through CDN for existing projects.
