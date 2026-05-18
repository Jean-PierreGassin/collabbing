<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustHosts;
use App\Jobs\SyncGitHubRepositories;
use Tests\TestCase;

class ProductionRuntimeContractTest extends TestCase
{
    public function testHealthCheckRouteIsAvailableForContainerRollouts(): void
    {
        $this->get(route('health'))->assertNoContent();
    }

    public function testProductionTrustedHostsAreDerivedFromConfiguredUrl(): void
    {
        config(['app.url' => 'https://collabbing.example.com']);

        $trustedHosts = (new TrustHosts($this->app))->hosts();

        $this->assertContains('^collabbing\.example\.com$', $trustedHosts);
        $this->assertNotContains('^evil\.example\.com$', $trustedHosts);
    }

    public function testQueueRetryWindowExceedsGithubSyncJobTimeout(): void
    {
        $job = new SyncGitHubRepositories;

        $this->assertGreaterThan($job->timeout, config('queue.connections.redis.retry_after'));
        $this->assertGreaterThan($job->timeout, config('queue.connections.database.retry_after'));
    }

    public function testProductionStackRunsWebQueueSchedulerAndCacheProcesses(): void
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

        $nginx = file_get_contents(base_path('docker/production/nginx.conf'));

        $this->assertIsString($nginx);
        $this->assertStringContainsString('map $http_x_forwarded_proto $forwarded_proto', $nginx);
        $this->assertStringContainsString('fastcgi_param HTTP_X_FORWARDED_HOST $host;', $nginx);
        $this->assertStringContainsString('fastcgi_param HTTP_X_FORWARDED_PROTO $forwarded_proto;', $nginx);
    }

    public function testMasterPushDeploysABuiltImageAfterVerification(): void
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
        $this->assertStringContainsString("permissions:\n  contents: read\n\nenv:", $workflow);
        $this->assertStringContainsString("    permissions:\n      contents: read\n      packages: write", $workflow);
        $this->assertStringContainsString("    permissions:\n      contents: read\n      packages: read", $workflow);
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

    public function testDeployScriptRunsDatabaseAndProcessRefreshSteps(): void
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
        $this->assertStringContainsString('run_artisan_with_retry migrate --force', $script);
        $this->assertStringContainsString('wait_for_service_replicas', $script);
        $this->assertStringContainsString('Timed out waiting for ${STACK_NAME}_${service}', $script);
        $this->assertStringContainsString('run_artisan queue:restart', $script);
        $this->assertStringContainsString('run_artisan schedule:interrupt', $script);
        $this->assertLessThan(
            strpos($script, 'docker stack deploy'),
            strpos($script, 'docker network inspect "$NETWORK_NAME"'),
        );
    }
}
