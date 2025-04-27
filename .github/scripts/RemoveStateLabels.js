export default async function ({github, context}) {
    const { data: labels } = await github.rest.issues.listLabelsOnIssue({
        issue_number: context.issue.number,
        owner: context.repo.owner,
        repo: context.repo.repo
    });

    const hasVisualTestsLabel = labels.find(label => label.name === 'run-visual-tests');
    if (hasVisualTestsLabel) {
        await github.rest.issues.removeLabel({
            issue_number: context.issue.number,
            name: 'run-visual-tests',
            owner: context.repo.owner,
            repo: context.repo.repo
        });
    }

    const hasPausePagesLabel = labels.find(label => label.name === 'pause-pages');
    if (hasPausePagesLabel) {
        await github.rest.issues.removeLabel({
            issue_number: context.issue.number,
            name: 'pause-pages',
            owner: context.repo.owner,
            repo: context.repo.repo
        });
    }

    const hasWTDLabel = labels.find(label => label.name === 'WTD');
    if (hasWTDLabel) {
        await github.rest.issues.removeLabel({
            issue_number: context.issue.number,
            name: 'WTD',
            owner: context.repo.owner,
            repo: context.repo.repo
        });
    }
}
