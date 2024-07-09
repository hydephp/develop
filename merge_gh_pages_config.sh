#!/bin/bash

# Ensure we're on the correct branch
git checkout version-control-gh-pages-configuration

# Get the list of commits from gh-pages-config branch, oldest first
commits=$(git log --reverse --format="%H" origin/gh-pages-config)

for commit in $commits
do
    # Get the commit message
    commit_msg=$(git log -1 --pretty=%B $commit)

    # Create a temporary index for this operation
    GIT_INDEX_FILE=".git/tmp-index" git read-tree $commit

    # Check out the files into the subdirectory
    GIT_INDEX_FILE=".git/tmp-index" git checkout-index -a --prefix=monorepo/gh-pages/gh-pages-config/

    # Add the changes
    git add monorepo/gh-pages/gh-pages-config

    # Create a new commit with original title and custom description
    git commit -m "$commit_msg" -m "Original commit: https://github.com/hydephp/develop/commit/$commit
From: $(git log -1 --pretty=%an $commit)
Date: $(git log -1 --pretty=%ad $commit)"

    # Clean up
    rm .git/tmp-index
done
