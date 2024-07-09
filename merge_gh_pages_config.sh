#!/bin/bash

git checkout version-control-gh-pages-configuration

commits=$(git log --reverse --format="%H" origin/gh-pages-config)

for commit in $commits
do
    commit_date=$(git log -1 --date=format:'%Y-%m-%d' --format=%cd $commit)
    commit_subject=$(git log -1 --format=%s $commit)
    commit_body=$(git log -1 --format=%b $commit)
    commit_author=$(git log -1 --format=%an $commit)
    full_commit_date=$(git log -1 --format=%ad $commit)

    GIT_INDEX_FILE=".git/tmp-index" git read-tree $commit

    files=$(GIT_INDEX_FILE=".git/tmp-index" git ls-tree -r --name-only $commit)

    for file in $files
    do
        GIT_INDEX_FILE=".git/tmp-index" git show $commit:$file > temp_file
        mkdir -p "monorepo/gh-pages/gh-pages-config/$(dirname "$file")"
        mv temp_file "monorepo/gh-pages/gh-pages-config/$file"
    done

    git add monorepo/gh-pages/gh-pages-config

    git commit -m "$commit_date: $commit_subject" -m "Subject: $commit_subject
From: $commit_author
Date: $full_commit_date

Original commit: https://github.com/hydephp/develop/commit/$commit

$commit_body"

    rm .git/tmp-index
done
