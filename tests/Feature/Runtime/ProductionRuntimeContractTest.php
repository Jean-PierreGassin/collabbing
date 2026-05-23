<?php

namespace Tests\Feature\Runtime;

use App\Http\Middleware\TrustHosts;
use App\Jobs\SyncGitHubRepositories;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('queueConnections')]
    public function testQueueRetryWindowExceedsGithubSyncJobTimeout(string $connection): void
    {
        $job = new SyncGitHubRepositories;

        $this->assertGreaterThan($job->timeout, config("queue.connections.{$connection}.retry_after"));
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

        $nginx = file_get_contents(base_path('docker/production/nginx.conf'));

        $this->assertIsString($nginx);
        $this->assertStringContainsString('map $http_x_forwarded_proto $forwarded_proto', $nginx);
        $this->assertStringContainsString('fastcgi_param HTTP_X_FORWARDED_HOST $host;', $nginx);
        $this->assertStringContainsString('fastcgi_param HTTP_X_FORWARDED_PROTO $forwarded_proto;', $nginx);
    }

    #[DataProvider('productionServiceUpdateOrders')]
    public function testProductionServicesUseExpectedUpdateOrder(string $service, string $order): void
    {
        $stack = Yaml::parseFile(base_path('docker/production/stack.yaml'));

        $this->assertSame($order, $stack['services'][$service]['deploy']['update_config']['order']);
    }

    public function testProductionWorkflowVerifiesBuildsAndKeepsDeployManual(): void
    {
        $workflow = Yaml::parseFile(base_path('.github/workflows/production-deploy.yml'));

        $this->assertIsArray($workflow);
        $this->assertArrayNotHasKey('push', $workflow['on']);
        $this->assertArrayHasKey('workflow_dispatch', $workflow['on']);
        $this->assertSame(['contents' => 'read'], $workflow['permissions']);
        $this->assertSame(['contents' => 'read', 'packages' => 'write'], $workflow['jobs']['build']['permissions']);
        $this->assertSame(['contents' => 'read', 'packages' => 'read'], $workflow['jobs']['deploy']['permissions']);
        $this->assertSame('verify', $workflow['jobs']['build']['needs']);
        $this->assertSame('build', $workflow['jobs']['deploy']['needs']);

        $deploySteps = collect($workflow['jobs']['deploy']['steps'])->keyBy('name');
        $deployRuns = implode("\n", $this->workflowStepRuns($workflow, 'deploy'));

        $this->assertSame('inputs.deploy', $deploySteps['Deploy over SSH']['if']);
        $this->assertStringContainsString('PRODUCTION_SSH_KNOWN_HOSTS', $deployRuns);
        $this->assertStringContainsString('docker/production/deploy.sh --preflight', $deployRuns);
        $this->assertStringNotContainsString('ssh-keyscan', $deployRuns);
    }

    #[DataProvider('productionWorkflowVerifyCommands')]
    public function testProductionWorkflowRunsRequiredVerificationCommand(string $expectedCommand): void
    {
        $workflow = Yaml::parseFile(base_path('.github/workflows/production-deploy.yml'));

        $this->assertContains($expectedCommand, $this->workflowStepRuns($workflow, 'verify'));
    }

    public function testDeployScriptRunsDatabaseAndProcessRefreshSteps(): void
    {
        $script = file_get_contents(base_path('docker/production/deploy.sh'));

        $this->assertIsString($script);
        $this->assertLessThan(
            strpos($script, 'docker swarm init'),
            strrpos($script, 'validate_production_env'),
        );
        $this->assertStringNotContainsString('. "$DOCKER_ENV_FILE"', $script);
        $this->assertLessThan(
            strpos($script, 'docker stack deploy'),
            strpos($script, 'docker network inspect "$NETWORK_NAME"'),
        );
    }

    #[DataProvider('deployScriptRuntimeSteps')]
    public function testDeployScriptIncludesExpectedRuntimeStep(string $expectedText): void
    {
        $script = file_get_contents(base_path('docker/production/deploy.sh'));

        $this->assertIsString($script);
        $this->assertStringContainsString($expectedText, $script);
    }

    private function workflowStepRuns(array $workflow, string $job): array
    {
        return collect($workflow['jobs'][$job]['steps'])
            ->pluck('run')
            ->filter()
            ->values()
            ->all();
    }

    public static function queueConnections(): array
    {
        return [
            'redis' => ['redis'],
            'database' => ['database'],
        ];
    }

    public static function productionServiceUpdateOrders(): array
    {
        return [
            'app' => ['app', 'start-first'],
            'nginx' => ['nginx', 'start-first'],
            'queue' => ['queue', 'start-first'],
            'scheduler' => ['scheduler', 'stop-first'],
        ];
    }

    public static function productionWorkflowVerifyCommands(): array
    {
        return [
            'pint' => ['vendor/bin/pint --test'],
            'phpstan' => ['vendor/bin/phpstan analyse --memory-limit=1G --debug'],
            'phpunit' => ['php artisan test --compact --do-not-cache-result'],
            'eslint' => ['bun run lint'],
            'typescript' => ['bun run typecheck'],
            'vitest' => ['bun run test'],
            'vite build' => ['bun run build'],
        ];
    }

    public static function deployScriptRuntimeSteps(): array
    {
        return [
            'swarm init' => ['docker swarm init'],
            'stack deploy' => ['docker stack deploy'],
            'missing environment file' => ['Missing production environment file'],
            'preflight failure' => ['Production deploy preflight failed'],
            'environment loading' => ['load_env_file'],
            'invalid environment line' => ['invalid environment line'],
            'environment validation' => ['validate_production_env'],
            'https app url' => ['APP_URL must use https'],
            'generated app key' => ['APP_KEY must be generated'],
            'network inspection' => ['docker network inspect "$NETWORK_NAME"'],
            'artisan helper' => ['php artisan "$@"'],
            'migrations' => ['run_artisan_with_retry migrate --force'],
            'replica wait' => ['wait_for_service_replicas'],
            'replica timeout' => ['Timed out waiting for ${STACK_NAME}_${service}'],
            'queue restart' => ['run_artisan queue:restart'],
            'schedule interrupt' => ['run_artisan schedule:interrupt'],
        ];
    }
}
