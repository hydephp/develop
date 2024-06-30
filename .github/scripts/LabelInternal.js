module.exports = ({github, context}) => {
    return github.rest.issues.addLabels({
        issue_number: context.issue.number,
        owner: context.repo.owner,
        repo: context.repo.repo,
        labels: ['Internal']
    })
}
