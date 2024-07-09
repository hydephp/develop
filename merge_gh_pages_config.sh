#!/bin/bash

# Ensure we're on the correct branch
git checkout version-control-gh-pages-configuration

# Get the list of commits from gh-pages-config branch, oldest first
commits=$(git log --reverse --format="%H" origin/gh-pages-config)

for commit in $commits
do
    # Cherry-pick the commit without committing
    git cherry-pick -n $commit

    # Get the list of files changed in this commit
    changed_files=$(git diff-tree --no-commit-id --name-only -r $commit)

    # Create the target directory if it doesn't exist
    mkdir -p monorepo/gh-pages/gh-pages-config

    # Move only the changed files to the subdirectory
    for file in $changed_files
    do
        if [ -f "$file" ]; then
            # Create the directory structure in the target location
            mkdir -p "monorepo/gh-pages/gh-pages-config/$(dirname "$file")"
            # Move the file
            git mv "$file" "monorepo/gh-pages/gh-pages-config/$file"
        fi
    done

    # Stage the moved files
    git add .

    # Create a new commit with original title and custom description
    git commit -m "$(git log -1 --pretty=%B $commit)" -m "Original commit: https://github.com/hydephp/develop/commit/$commit
From: $(git log -1 --pretty=%an $commit)
Date: $(git log -1 --pretty=%ad $commit)"
done
