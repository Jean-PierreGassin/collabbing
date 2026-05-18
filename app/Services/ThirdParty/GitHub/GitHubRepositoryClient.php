<?php

namespace App\Services\ThirdParty\GitHub;

use App\Models\User;
use Github\Client;
use RuntimeException;

class GitHubRepositoryClient
{
    public function __construct(private GitHubClientFactory $clients) {}

    public function create(User $owner, string $repositoryName): array
    {
        return $this->client($owner)
            ->repo()
            ->create($repositoryName);
    }

    public function show(User $owner, string $repositoryName): array
    {
        return $this->client($owner)
            ->repo()
            ->show($this->username($owner), $repositoryName);
    }

    public function latestCommit(User $owner, string $repositoryName, ?string $branch): ?array
    {
        $commits = $this->client($owner)
            ->repo()
            ->commits()
            ->all($this->username($owner), $repositoryName, array_filter([
                'sha' => $branch,
                'per_page' => 1,
            ]));

        return $commits[0] ?? null;
    }

    public function addCollaborator(User $owner, string $repositoryName, string $collaboratorUsername): void
    {
        $this->client($owner)
            ->repo()
            ->collaborators()
            ->add($this->username($owner), $repositoryName, $collaboratorUsername);
    }

    private function client(User $owner): Client
    {
        $token = $owner->githubToken();

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('GitHub token is missing.');
        }

        return $this->clients->create($token);
    }

    private function username(User $owner): string
    {
        $username = $owner->githubUsername();

        if (! is_string($username) || $username === '') {
            throw new RuntimeException('GitHub username is missing.');
        }

        return $username;
    }
}
