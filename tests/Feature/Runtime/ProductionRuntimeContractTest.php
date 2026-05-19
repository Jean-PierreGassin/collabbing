<?php

namespace Tests\Feature\Runtime;

use App\Http\Middleware\TrustHosts;
use App\Jobs\SyncGitHubRepositories;
use Symfony\Component\Yaml\Yaml;
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

    public function testProductionStackDefinesRequiredRuntimeServices(): void
    {
        $stack = Yaml::parseFile(base_path('docker/production/stack.yaml'));

        $this->assertIsArray($stack);
        $this->assertArrayHasKey('services', $stack);
        $this->assertEqualsCanonicalizing(
            ['app', 'mysql', 'nginx', 'queue', 'redis', 'scheduler'],
            array_keys($stack['services']),
        );
        $this->assertStringContainsString('queue:work redis', implode(' ', $stack['services']['queue']['command']));
        $this->assertStringContainsString('schedule:work', implode(' ', $stack['services']['scheduler']['command']));
        $this->assertSame('start-first', $stack['services']['app']['deploy']['update_config']['order']);
        $this->assertSame('start-first', $stack['services']['nginx']['deploy']['update_config']['order']);
        $this->assertSame('start-first', $stack['services']['queue']['deploy']['update_config']['order']);
        $this->assertSame('stop-first', $stack['services']['scheduler']['deploy']['update_config']['order']);

        $nginx = file_get_contents(base_path('docker/production/nginx.conf'));

        $this->assertIsString($nginx);
        $this->assertStringContainsString('map $http_x_forwarded_proto $forwarded_proto', $nginx);
        $this->assertStringContainsString('fastcgi_param HTTP_X_FORWARDED_HOST $host;', $nginx);
        $this->assertStringContainsString('fastcgi_param HTTP_X_FORWARDED_PROTO $forwarded_proto;', $nginx);
    }

    public function testProductionWorkflowVerifiesBuildsAndDeploysReleaseImage(): void
    {
        $workflow = Yaml::parseFile(base_path('.github/workflows/production-deploy.yml'));

        $this->assertIsArray($workflow);
        $this->assertContains('master', $workflow['on']['push']['branches']);
        $this->assertArrayHasKey('workflow_dispatch', $workflow['on']);
        $this->assertSame(['contents' => 'read'], $workflow['permissions']);
        $this->assertSame(['contents' => 'read', 'packages' => 'write'], $workflow['jobs']['build']['permissions']);
        $this->assertSame(['contents' => 'read', 'packages' => 'read'], $workflow['jobs']['deploy']['permissions']);
        $this->assertSame('verify', $workflow['jobs']['build']['needs']);
        $this->assertSame('build', $workflow['jobs']['deploy']['needs']);

        $verifyRuns = $this->workflowStepRuns($workflow, 'verify');

        foreach ([
            'vendor/bin/pint --test',
            'vendor/bin/phpstan analyse --memory-limit=1G --debug',
            'php artisan test --compact --do-not-cache-result',
            'npm run lint',
            'npm run typecheck',
            'npm run test',
            'npm run build',
        ] as $expectedCommand) {
            $this->assertContains($expectedCommand, $verifyRuns);
        }

        $deployRuns = implode("\n", $this->workflowStepRuns($workflow, 'deploy'));

        $this->assertStringContainsString('PRODUCTION_SSH_KNOWN_HOSTS', $deployRuns);
        $this->assertStringContainsString('docker/production/deploy.sh --preflight', $deployRuns);
        $this->assertStringNotContainsString('ssh-keyscan', $deployRuns);
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

    private function workflowStepRuns(array $workflow, string $job): array
    {
        return collect($workflow['jobs'][$job]['steps'])
            ->pluck('run')
            ->filter()
            ->values()
            ->all();
    }
}
