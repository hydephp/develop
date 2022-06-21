# Monorepo CI/Manual Splitting Overview

```mermaid
flowchart LR
    p(Changes to monorepo/master) --> ci(Continuous Integration)
   
    subgraph split-monorepo.yml
        subgraph handle hyde
            ci --> shd[apply transformations and split into mirror branch] --> dhd(push mirror to readonly repositories)
        end 
        subgraph handle framework
            ci --> pfr[package source directory to artifact] --> sfr(split into mirror branch) --> dfr(push mirror to readonly repositories)
        end
        subgraph handle realtime-compiler
            ci --> prc[package source directory to artifact] --> src(split into mirror branch) --> drc(push mirror to readonly repositories)
        end
    end
```