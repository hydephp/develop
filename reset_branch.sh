#!/bin/bash

# Ensure we're on the correct branch
git checkout version-control-gh-pages-configuration

# Get the current branch name
current_branch=$(git rev-parse --abbrev-ref HEAD)

# Create a temporary branch
temp_branch="${current_branch}_temp"
git checkout -b $temp_branch

# Get all commit hashes
commits=$(git log --format=%H)

# Iterate through commits in reverse order
for commit in $commits; do
    # Check if the commit is a merge commit
    if [ $(git rev-parse $commit^2 2>/dev/null) ]; then
        echo "Skipping merge commit: $commit"
    else
        # Cherry-pick non-merge commits
        git cherry-pick $commit
    fi
done

# Rename branches
git branch -m $current_branch "${current_branch}_old"
git branch -m $temp_branch $current_branch

echo "Merge commits have been removed. The original branch has been renamed to ${current_branch}_old"
