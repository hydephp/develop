#!/bin/bash

# Ensure we're on the correct branch
git checkout version-control-gh-pages-configuration

# Get the list of commits from gh-pages-config branch, oldest first
commits=$(git log --reverse --format="%H" origin/gh-pages-config)

for commit in $commits
do
 # Cherry-pick the commit without committing
 git cherry-pick -n $commit

 # Move files to the subdirectory (create if it doesn't exist)
 mkdir -p monorepo/gh-pages/gh-pages-config
 git mv -k * monorepo/gh-pages/gh-pages-config/

 # Stage the moved files
 git add .

 # Create a new commit with original title and custom description
 git commit -m "$(git log -1 --pretty=%B $commit)" -m "Original commit: https://github.com/hydephp/develop/commit/$commit
From: $(git log -1 --pretty=%an $commit)
Date: $(git log -1 --pretty=%ad $commit)"
done
