<?php

namespace Tests\Feature;

use App\Jobs\SyncGitHubRepositories;
use Tests\TestCase;

class ProductionRuntimeContractTest extends TestCase
{
    public function test_health_check_route_is_available_for_container_rollouts(): void
    {
        $this->get(route('health'))->assertNoContent();
    }

    public function test_queue_retry_window_exceeds_github_sync_job_timeout(): void
    {
        $job = new SyncGitHubRepositories;

        $this->assertGreaterThan($job->timeout, config('queue.connections.redis.retry_after'));
        $this->assertGreaterThan($job->timeout, config('queue.connections.database.retry_after'));
    }

    public function test_production_stack_runs_web_queue_scheduler_and_cache_processes(): void
    {
        $stack = file_get_contents(base_path('docker/production/stack.yaml'));

        $this->assertIsString($stack);
        $this->assertStringContainsString('app:', $stack);
        $this->assertStringContainsString('nginx:', $stack);
        $this->assertStringContainsString('queue:', $stack);
        $this->assertStringContainsString('scheduler:', $stack);
        $this->assertStringContainsString('redis:', $stack);
        $this->assertStringContainsString('queue:work', $stack);
        $this->assertStringContainsString('schedule:work', $stack);
        $this->assertStringContainsString('order: start-first', $stack);
    }

    public function test_master_push_deploys_a_built_image_after_verification(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/production-deploy.yml'));

        $this->assertIsString($workflow);
        $this->assertStringContainsString('branches:', $workflow);
        $this->assertStringContainsString('- master', $workflow);
        $this->assertStringContainsString('workflow_dispatch:', $workflow);
        $this->assertStringContainsString('Run the full deployment after preflight', $workflow);
        $this->assertStringContainsString('Run PHPUnit', $workflow);
        $this->assertStringContainsString('Build frontend', $workflow);
        $this->assertStringContainsString('Build and push image', $workflow);
        $this->assertStringContainsString('Validate deployment secrets', $workflow);
        $this->assertStringContainsString('Missing ${name}', $workflow);
        $this->assertStringContainsString('Run deployment preflight', $workflow);
        $this->assertStringContainsString('Deploy over SSH', $workflow);
        $this->assertStringContainsString('docker login ghcr.io', $workflow);
        $this->assertStringContainsString('docker/production/deploy.sh --preflight', $workflow);
        $this->assertStringContainsString("if: github.event_name == 'push' || inputs.deploy", $workflow);
        $this->assertStringContainsString('PRODUCTION_SSH_KNOWN_HOSTS', $workflow);
        $this->assertStringNotContainsString('echo \'${{ secrets.GITHUB_TOKEN }}\'', $workflow);
        $this->assertStringNotContainsString('ssh-keyscan', $workflow);
    }

    public function test_deploy_script_runs_database_and_process_refresh_steps(): void
    {
        $script = file_get_contents(base_path('docker/production/deploy.sh'));

        $this->assertIsString($script);
        $this->assertStringContainsString('docker swarm init', $script);
        $this->assertStringContainsString('docker stack deploy', $script);
        $this->assertStringContainsString('Missing production environment file', $script);
        $this->assertStringContainsString('Production deploy preflight failed', $script);
        $this->assertStringContainsString('load_env_file', $script);
        $this->assertStringContainsString('invalid environment line', $script);
        $this->assertStringContainsString('validate_production_env', $script);
        $this->assertStringContainsString('APP_URL must use https', $script);
        $this->assertStringContainsString('APP_KEY must be generated', $script);
        $this->assertLessThan(
            strpos($script, 'docker swarm init'),
            strrpos($script, 'validate_production_env'),
        );
        $this->assertStringNotContainsString('. "$DOCKER_ENV_FILE"', $script);
        $this->assertStringContainsString('docker network inspect "$NETWORK_NAME"', $script);
        $this->assertStringContainsString('php artisan "$@"', $script);
        $this->assertStringContainsString('run_artisan migrate --force', $script);
        $this->assertStringContainsString('run_artisan queue:restart', $script);
        $this->assertStringContainsString('run_artisan schedule:interrupt', $script);
        $this->assertLessThan(
            strpos($script, 'docker stack deploy'),
            strpos($script, 'docker network inspect "$NETWORK_NAME"'),
        );
    }
}
