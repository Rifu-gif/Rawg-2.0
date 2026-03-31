# GitHub Workflow Documentation

## Brief Explanation of Git Workflow
This project followed a feature-branch Git workflow, where the main branch remained the stable codebase. A separate branch called feature/game-recommendation-system was created for the new feature, and all development including multiple commits during building and testing happened there. After the feature was finished, the commit history was squashed into a single clean commit, and the branch was prepared for review and merging into main. This approach keeps the main branch tidy, isolates feature work from stable code, and improves the clarity of the project history.

## Screenshots of Branch Before and After Squashing
The screenshots below show the branch history before and after squashing.

### After Squashing
The "after squashing" screenshot was taken from the current branch history using:

```powershell
git log --oneline --decorate --graph --all -n 20
```

The result shows the feature branch as one clean commit:

```text
* b3baaeb (HEAD -> feature/game-recommendation-system, origin/feature/game-recommendation-system) feat: game recommendation system
```

Screenshot:

![After Squashing](./After%20Squashing%20.png)

### Before Squashing
The "before squashing" screenshot was taken from the reflog history using:

```powershell
git reflog --oneline
```

The reflog shows the earlier separate commits before they were combined, including:

- `0b1f24a` migrate frontend to Next.js and add API integrations
- `dee4239` Unit and integration testing done
- `815115d` updated frontend
- `2dbb05b` update
- `c7cb206` task 2

This confirms that the branch originally had multiple commits before squashing.

Screenshot:

![Before Squashing](./Before%20Squashing%20.png)

## Brief Explanation of How Squashing Was Performed
Squashing was used to combine multiple development commits into one final commit so the branch history would be cleaner and easier to review.

The process was:

1. Checkout the feature branch:

```powershell
git checkout feature/game-recommendation-system
```

2. Start an interactive rebase from the base branch:

```powershell
git rebase -i main
```

3. In the rebase editor, keep the first commit as `pick` and change the remaining commits to `squash` or `s`.

4. Save and close the editor, then confirm the final combined commit message.

5. Push the rewritten history to GitHub:

```powershell
git push --force-with-lease origin feature/game-recommendation-system
```

After squashing, the feature branch contained one polished commit representing the completed work instead of several smaller intermediate commits.

In this project, the reflog also showed `rebase (squash)` and `rebase (finish)` entries, which confirmed that an interactive rebase squash had been performed successfully.

## Pull Request Creation
A pull request was created from `feature/game-recommendation-system` into `main` after the branch had been squashed into one final commit. The pull request included a title and description summarizing the feature, the main changes made, the Git workflow used, and the testing that was carried out.

The pull request was intentionally left open and not merged into `main`, which satisfies the requirement of showing a proper PR with a description while keeping the main branch unchanged.
