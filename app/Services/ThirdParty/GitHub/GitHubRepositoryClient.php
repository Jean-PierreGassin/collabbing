<?php

namespace App\Services\ThirdParty\GitHub;

use App\Models\User;

class GitHubRepositoryClient
{
    public function create(User $owner, string $repositoryName): array
    {
        return GitHubService::createClient($owner->github_token)
            ->repo()
            ->create($repositoryName);
    }

    public function show(User $owner, string $repositoryName): array
    {
        return GitHubService::createClient($owner->github_token)
            ->repo()
            ->show($owner->github_username, $repositoryName);
    }

    public function latestCommit(User $owner, string $repositoryName, ?string $branch): ?array
    {
        $commits = GitHubService::createClient($owner->github_token)
            ->repo()
            ->commits()
            ->all($owner->github_username, $repositoryName, array_filter([
                'sha' => $branch,
                'per_page' => 1,
            ]));

        return $commits[0] ?? null;
    }

    public function addCollaborator(User $owner, string $repositoryName, string $collaboratorUsername): void
    {
        GitHubService::createClient($owner->github_token)
            ->repo()
            ->collaborators()
            ->add($owner->github_username, $repositoryName, $collaboratorUsername);
    }
}
