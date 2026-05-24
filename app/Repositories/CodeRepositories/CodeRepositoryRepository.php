<?php

namespace App\Repositories\CodeRepositories;

use App\Models\CodeRepository;
use Illuminate\Support\Collection;

class CodeRepositoryRepository
{
    public function dueForGitHubSync(int $limit): Collection
    {
        return CodeRepository::query()
            ->where('provider', CodeRepository::PROVIDER_GITHUB)
            ->where('status', CodeRepository::STATUS_ACTIVE)
            ->where(fn ($query) => $query
                ->whereNull('sync_due_at')
                ->orWhere('sync_due_at', '<=', now()))
            ->whereHas('idea.user.githubAccount', fn ($query) => $query
                ->whereNotNull('token')
                ->whereNotNull('provider_username'))
            ->with('idea.user.githubAccount')
            ->oldest('sync_due_at')
            ->limit($limit)
            ->get();
    }

    public function forceUpdate(CodeRepository $codeRepository, array $attributes): bool
    {
        return $codeRepository
            ->forceFill($attributes)
            ->save();
    }
}
