# Monorepo CI/Manual Splitting Overview

```mermaid
flowchart LR
    p(Changes to monorepo/master) --> ci(Continuous Integration)
   
    subgraph split-monorepo.yml 
        ci --> hhd(handle hyde) --> shd[apply transformations and split into mirror branch] --> dhd(push mirror to readonly repositories)
        ci --> hfr(handle framework)          --> pfr[package source directory to artifact] --> sfr(split into mirror branch) --> dfr(push mirror to readonly repositories)
        ci --> hrc(handle realtime-compiler)  --> prc[package source directory to artifact] --> src(split into mirror branch) --> drc(push mirror to readonly repositories)
    end
```