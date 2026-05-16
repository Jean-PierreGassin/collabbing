<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->string('repository_html_url')->nullable()->after('repository_name');
            $table->string('repository_default_branch')->nullable()->after('repository_html_url');
            $table->unsignedInteger('repository_open_issues_count')->default(0)->after('repository_default_branch');
            $table->unsignedInteger('repository_stargazers_count')->default(0)->after('repository_open_issues_count');
            $table->unsignedInteger('repository_forks_count')->default(0)->after('repository_stargazers_count');
            $table->string('repository_latest_commit_sha')->nullable()->after('repository_forks_count');
            $table->string('repository_latest_commit_message')->nullable()->after('repository_latest_commit_sha');
            $table->string('repository_latest_commit_author')->nullable()->after('repository_latest_commit_message');
            $table->timestamp('repository_pushed_at')->nullable()->after('repository_latest_commit_author');
            $table->timestamp('repository_synced_at')->nullable()->after('repository_pushed_at');
            $table->timestamp('repository_missing_at')->nullable()->after('repository_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->dropColumn([
                'repository_html_url',
                'repository_default_branch',
                'repository_open_issues_count',
                'repository_stargazers_count',
                'repository_forks_count',
                'repository_latest_commit_sha',
                'repository_latest_commit_message',
                'repository_latest_commit_author',
                'repository_pushed_at',
                'repository_synced_at',
                'repository_missing_at',
            ]);
        });
    }
};
