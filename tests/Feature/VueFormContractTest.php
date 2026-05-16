<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VueFormContractTest extends TestCase
{
    #[DataProvider('vueFormFields')]
    public function test_vue_forms_include_expected_backend_fields(string $path, array $fields): void
    {
        $contents = file_get_contents(resource_path($path));

        $this->assertIsString($contents);

        foreach ($fields as $field) {
            $this->assertStringContainsString(
                "name=\"{$field}\"",
                $contents,
                "Expected {$path} to include a {$field} field."
            );
        }
    }

    public static function vueFormFields(): array
    {
        return [
            'register' => [
                'js/pages/Auth/Register.vue',
                ['username', 'first_name', 'last_name', 'email', 'password', 'password_confirmation'],
            ],
            'password reset' => [
                'js/pages/Auth/PasswordReset.vue',
                ['token', 'email', 'password', 'password_confirmation'],
            ],
            'profile' => [
                'js/pages/Users/Form.vue',
                ['first_name', 'last_name', 'email', 'bio', 'password', 'password_confirmation'],
            ],
            'idea' => [
                'js/pages/Ideas/Form.vue',
                ['title', 'repository_name', 'communication', 'content', 'status'],
            ],
            'comment' => [
                'js/pages/Comments/Form.vue',
                ['content'],
            ],
            'application' => [
                'js/pages/Ideas/Apply.vue',
                ['content'],
            ],
        ];
    }
}
