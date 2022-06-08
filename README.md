# HydePHP - Monorepo Experiment

Experimenting with a monorepo. The way I'm thinking is that this repo (when/if transferred to the Hyde org)
is the master origin and source of truth for all HydePHP packages. When a release is tagged here, a CI action
propagates the tag and changes to all* HydePHP packages and creates subsequent releases.

Some packages, like hyde/hyde will need some processing after the merge. For example, the composer.json
should be reverted to not use the dev-master branch.

*Some packages, like HydeFront may not need to have their tags synced. Thinking it is mainly for
hyde/hyde and hyde/framework as they are tightly coupled.