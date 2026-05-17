<?php

namespace App\Services\ThirdParty\GitHub;

use App\Models\User;

class GitHubRepositoryClient
{
    public function create(User $owner, string $repositoryName): array
    {
        return GitHubService::createClient($owner->githubToken())
            ->repo()
            ->create($repositoryName);
    }

    public function show(User $owner, string $repositoryName): array
    {
        return GitHubService::createClient($owner->githubToken())
            ->repo()
            ->show($owner->githubUsername(), $repositoryName);
    }

    public function latestCommit(User $owner, string $repositoryName, ?string $branch): ?array
    {
        $commits = GitHubService::createClient($owner->githubToken())
            ->repo()
            ->commits()
            ->all($owner->githubUsername(), $repositoryName, array_filter([
                'sha' => $branch,
                'per_page' => 1,
            ]));

        return $commits[0] ?? null;
    }

    public function addCollaborator(User $owner, string $repositoryName, string $collaboratorUsername): void
    {
        GitHubService::createClient($owner->githubToken())
            ->repo()
            ->collaborators()
            ->add($owner->githubUsername(), $repositoryName, $collaboratorUsername);
    }
}
