#!/bin/bash

# Ensure we're on the correct branch
git checkout version-control-gh-pages-configuration

# Get the current branch name
current_branch=$(git rev-parse --abbrev-ref HEAD)

# Create a temporary branch
temp_branch="${current_branch}_temp"
git checkout -b $temp_branch

# Get the starting commit
start_commit="91ac6c5366d02d9f906d5e25463c7f3edae6f165"

# Get all commit hashes from the starting commit to HEAD
commits=$(git rev-list --reverse $start_commit..HEAD)

# Iterate through commits
for commit in $commits; do
    # Check if the commit message contains "Original commit:"
    if git log -1 --format=%B $commit | grep -q "Original commit: https://github.com/hydephp/develop/commit/"; then
        echo "Skipping commit: $commit"
    else
        # Cherry-pick commits that don't contain the "Original commit:" text
        git cherry-pick $commit
    fi
done

# Rename branches
git branch -m $current_branch "${current_branch}_old"
git branch -m $temp_branch $current_branch

echo "Commits with 'Original commit:' have been removed. The original branch has been renamed to ${current_branch}_old"
