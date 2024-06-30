module.exports = async ({github, context}) => {
    const { data: labels } = await github.rest.issues.listLabelsOnIssue({
        issue_number: context.issue.number,
        owner: context.repo.owner,
        repo: context.repo.repo
    });

    const visualTestsLabel = labels.find(label => label.name === 'run-visual-tests');
    if (visualTestsLabel) {
        await github.rest.issues.removeLabel({
            issue_number: context.issue.number,
            name: 'run-visual-tests',
            owner: context.repo.owner,
            repo: context.repo.repo
        });
    }
}
