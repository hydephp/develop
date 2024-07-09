#!/bin/bash

set -e

git checkout version-control-gh-pages-configuration

current_branch=$(git rev-parse --abbrev-ref HEAD)
temp_branch="${current_branch}_temp"

if git show-ref --verify --quiet refs/heads/$temp_branch; then
    git branch -D $temp_branch
fi

git checkout -b $temp_branch

start_commit="91ac6c5366d02d9f906d5e25463c7f3edae6f165"
commits=$(git rev-list --reverse $start_commit..HEAD)

for commit in $commits; do
    if git log -1 --format=%B $commit | grep -q "Original commit: https://github.com/hydephp/develop/commit/"; then
        echo "Skipping commit: $commit"
    else
        if ! git cherry-pick $commit; then
            git cherry-pick --abort
            echo "Skipping commit due to conflict: $commit"
        fi
    fi
done

if git show-ref --verify --quiet refs/heads/${current_branch}_old; then
    git branch -D ${current_branch}_old
fi

git branch -m $current_branch "${current_branch}_old"
git branch -m $temp_branch $current_branch

echo "Process completed."
