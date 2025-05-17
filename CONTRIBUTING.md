# Contributing

Contributions are **welcome** and will be fully **credited**.

Please read and understand the contribution guide before creating an issue or pull request. This document is a living standard that may be updated when needed.


## Resources

If you're new to HydePHP and are looking to contribute, it may be helpful to learn how the ecosystem and core development works.
It's thus highly advised you visit our [Developer Resources](https://hydephp.com/community) to learn how HydePHP is structured.


## Etiquette

This project is open source, and as such, the maintainers give their free time to build and maintain the source code
held within. They make the code freely available in the hope that it will be of use to other developers. It would be
extremely unfair for them to suffer abuse or anger for their hard work.

Please be considerate towards maintainers when raising issues or presenting pull requests. Let's show the
world that developers are civilized and selfless people.

It's the duty of the maintainer to ensure that all submissions to the project are of sufficient
quality to benefit the project. Many developers have different skill sets, strengths, and weaknesses. Respect the maintainer's decision, and do not be upset or abusive if your submission is not used.


## Viability

When requesting or submitting new features, first consider whether they might be useful to others. Open
source projects are used by many developers, who may have entirely different needs from your own. Think about
whether or not your feature is likely to be used by other users of the project, or if your feature may instead be better suited as a third party extension.

You may also want to make sure that your feature abides by the goals of HydePHP which are as follows:

- **Developer experience first:** Creating sites with Hyde and contributing to the framework should be a joy.
- **Zero config setup:** You should be able to just install Hyde and start building your project right away. Hyde should follow convention over configuration and come preconfigured with sensible defaults.
- **Customizable when you need it:** While configuration should not be a requirement, the option should always be there. As such, we should provide easy ways for customization to those who need it.

When thinking about a new feature, make sure it's intuitive and easy to understand without having to refer to the docs all the time. The most intuitive workflow is often the best one. If a feature requires much explanation to be used and understood, it might need to be simplified.

## Which Branch?

All bug fixes should be sent to the latest version that supports bug fixes (currently 1.x). Bug fixes should never be sent to the master branch unless they fix features that exist only in the upcoming release.

Minor features that are fully backward compatible with the current release may be sent to the latest stable branch (currently 1.x).

Major new features or features with breaking changes should always be sent to the master branch, which contains the upcoming release.

## Procedure

Before filing an issue:

- Attempt to replicate the problem, to ensure that it wasn't a coincidental incident.
- Check to make sure your feature suggestion isn't already present within the project.
- Check the pull requests tab to ensure that the bug doesn't have a fix in progress.
- Check the pull requests tab to ensure that the feature isn't already in progress.

Before submitting a pull request:

- Check the codebase to ensure that your feature doesn't already exist.
- Check the pull requests to ensure that another person hasn't already submitted the feature or fix.
- Check the feature is a viable for the project (see above)


## How-to

HydePHP development is made in the HydePHP monorepo found at https://github.com/hydephp/develop.
To get started, you will need to clone the repository, and run `composer install`. You will then be able to make changes to the packages found in the `packages/` sub-directories.

Once you've made and commited your changes, submit a pull request to the same repository, and explain your changes and how they improve the codebase.


## Requirements

If the project maintainer has any additional requirements, you will find them listed here.

We try to follow the Laravel standards, https://laravel.com/docs/10.x/contributions#coding-style

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)**

**Please add tests!**
- **Add tests!** - Your patch might not be accepted if it doesn't have tests. When submitting a bug fix, make sure to include one or more tests proving the fix works, When adding features, make sure all aspects are properly tested.

- **Document any change in behaviour** - Make sure the `README.md` and the project documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](https://semver.org/). Randomly breaking public APIs is not an option.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests. This makes it easier to keep track of changes.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](https://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting. Making atomic commits eases the burden on the developer reviewing your pull request.

**Happy coding**!
