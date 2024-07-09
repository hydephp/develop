#!/bin/bash

git checkout version-control-gh-pages-configuration

# Get the number of commits in the branch
commit_count=$(git rev-list --count HEAD)

# Start an interactive rebase
git rebase -i HEAD~$commit_count

# Create a temporary file for the rebase script
temp_file=$(mktemp)

# Write the rebase script
echo "#!/bin/bash

while read old_commit new_commit rest; do
    if [ -z "\$new_commit" ]; then
        new_commit=\$old_commit
    fi

    commit_msg=\$(git log --format=%B -n 1 \$new_commit)
    if [[ \$commit_msg == *"Original commit:"* ]]; then
        commit_date=\$(git log -1 --date=format:'%Y-%m-%d' --format=%cd \$new_commit)
        commit_subject=\$(git log -1 --format=%s \$new_commit)
        commit_author=\$(git log -1 --format=%an \$new_commit)
        full_commit_date=\$(git log -1 --format=%ad \$new_commit)
        original_commit=\$(echo \"\$commit_msg\" | grep 'Original commit:' | sed 's/Original commit: //')

        new_msg=\"\$commit_date: \$commit_subject

From: \$commit_author
Date: \$full_commit_date
Subject: \$commit_subject

Original commit: \$original_commit\"

        git commit --amend -m \"\$new_msg\"
    fi
done

" > $temp_file

# Make the script executable
chmod +x $temp_file

# Set the rebase environment to use our script
export GIT_SEQUENCE_EDITOR=$temp_file

# Perform the rebase
git rebase --continue

# Clean up
rm $temp_file
