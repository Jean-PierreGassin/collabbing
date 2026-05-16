<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VueFormContractTest extends TestCase
{
    public function test_page_components_include_a_page_level_heading(): void
    {
        $pages = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('js/pages'))
        );

        foreach ($pages as $page) {
            if (! $page instanceof \SplFileInfo || $page->getExtension() !== 'vue') {
                continue;
            }

            $contents = file_get_contents($page->getPathname());

            $this->assertIsString($contents);
            $this->assertStringContainsString(
                '<h1',
                $contents,
                "Expected {$page->getPathname()} to include a page-level h1."
            );
        }
    }

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

    public function test_shared_form_field_exposes_accessible_help_and_error_state_to_controls(): void
    {
        $contents = file_get_contents(resource_path('js/components/forms/FormField.vue'));

        $this->assertIsString($contents);
        $this->assertStringContainsString(':described-by="describedBy"', $contents);
        $this->assertStringContainsString(':id="helpId"', $contents);
        $this->assertStringContainsString(':id="errorId"', $contents);
        $this->assertStringContainsString('role="alert"', $contents);
    }

    public function test_auth_forms_include_browser_and_assistive_technology_contracts(): void
    {
        $register = file_get_contents(resource_path('js/pages/Auth/Register.vue'));
        $login = file_get_contents(resource_path('js/pages/Auth/Login.vue'));
        $passwordEmail = file_get_contents(resource_path('js/pages/Auth/PasswordEmail.vue'));
        $passwordReset = file_get_contents(resource_path('js/pages/Auth/PasswordReset.vue'));
        $profile = file_get_contents(resource_path('js/pages/Users/Form.vue'));

        $this->assertIsString($register);
        $this->assertIsString($login);
        $this->assertIsString($passwordEmail);
        $this->assertIsString($passwordReset);
        $this->assertIsString($profile);

        foreach ([$register, $login, $passwordEmail, $passwordReset, $profile] as $contents) {
            $this->assertStringContainsString(':aria-invalid="invalid || undefined"', $contents);
            $this->assertStringContainsString(':aria-describedby="describedBy"', $contents);
        }

        $this->assertStringContainsString('autocomplete="username"', $register);
        $this->assertStringContainsString('autocomplete="current-password"', $login);
        $this->assertStringContainsString('autocomplete="email"', $passwordEmail);
        $this->assertStringContainsString('autocomplete="email"', $passwordReset);
        $this->assertStringContainsString('autocomplete="new-password"', $register);
        $this->assertStringContainsString('autocomplete="new-password"', $passwordReset);
        $this->assertStringContainsString('autocomplete="new-password"', $profile);
        $this->assertStringContainsString('minlength="12"', $register);
        $this->assertStringContainsString('minlength="12"', $passwordReset);
        $this->assertStringContainsString('maxlength="128"', $profile);
    }

    public function test_collaboration_forms_include_browser_and_assistive_technology_contracts(): void
    {
        $idea = file_get_contents(resource_path('js/pages/Ideas/Form.vue'));
        $comment = file_get_contents(resource_path('js/pages/Comments/Form.vue'));
        $inlineComment = file_get_contents(resource_path('js/pages/Ideas/Show.vue'));
        $application = file_get_contents(resource_path('js/pages/Ideas/Apply.vue'));
        $profile = file_get_contents(resource_path('js/pages/Users/Form.vue'));

        foreach ([$idea, $comment, $inlineComment, $application, $profile] as $contents) {
            $this->assertIsString($contents);
            $this->assertStringContainsString(':aria-invalid="invalid || undefined"', $contents);
            $this->assertStringContainsString(':aria-describedby="describedBy"', $contents);
        }

        $this->assertStringContainsString('maxlength="100"', $idea);
        $this->assertStringContainsString('maxlength="50"', $idea);
        $this->assertStringContainsString('pattern="[A-Za-z0-9_-]+"', $idea);
        $this->assertStringContainsString('maxlength="1500"', $idea);
        $this->assertStringContainsString('maxlength="1500"', $comment);
        $this->assertStringContainsString('maxlength="1500"', $inlineComment);
        $this->assertStringContainsString('maxlength="1500"', $application);
        $this->assertStringContainsString('maxlength="500"', $profile);
        $this->assertStringContainsString('form="github-revoke-form"', $profile);
        $this->assertStringContainsString('id="github-revoke-form"', $profile);
    }

    public function test_flash_messages_are_announced_to_assistive_technology(): void
    {
        $contents = file_get_contents(resource_path('js/components/layout/FlashMessages.vue'));

        $this->assertIsString($contents);
        $this->assertStringContainsString('role="status"', $contents);
        $this->assertStringContainsString('aria-live="polite"', $contents);
        $this->assertStringContainsString('role="alert"', $contents);
        $this->assertStringContainsString('aria-live="assertive"', $contents);
    }

    public function test_app_shell_provides_a_keyboard_bypass_to_main_content(): void
    {
        $contents = file_get_contents(resource_path('js/components/layout/AppShell.vue'));

        $this->assertIsString($contents);
        $this->assertStringContainsString('href="#main-content"', $contents);
        $this->assertStringContainsString('Skip to main content', $contents);
        $this->assertStringContainsString('<main id="main-content" tabindex="-1"', $contents);
        $this->assertStringContainsString('focus:translate-y-0', $contents);
    }

    public function test_collaboration_copy_uses_clear_actions_and_state_instead_of_placeholder_links(): void
    {
        $sidebar = file_get_contents(resource_path('js/components/ideas/IdeaSidebar.vue'));
        $ideasIndex = file_get_contents(resource_path('js/pages/Ideas/Index.vue'));
        $ideasManage = file_get_contents(resource_path('js/pages/Ideas/Manage.vue'));
        $dashboard = file_get_contents(resource_path('js/pages/Dashboard.vue'));
        $profile = file_get_contents(resource_path('js/pages/Users/Show.vue'));
        $pagination = file_get_contents(resource_path('js/components/pagination/PaginationLinks.vue'));
        $ideaController = file_get_contents(app_path('Http/Controllers/IdeaController.php'));
        $applicationController = file_get_contents(app_path('Http/Controllers/IdeaApplicationController.php'));
        $socialController = file_get_contents(app_path('Http/Controllers/Auth/SocialController.php'));
        $errorPage = file_get_contents(resource_path('js/pages/Error.vue'));

        foreach ([$sidebar, $ideasIndex, $ideasManage, $dashboard, $profile, $pagination, $ideaController, $applicationController, $socialController, $errorPage] as $contents) {
            $this->assertIsString($contents);
            $this->assertStringNotContainsString('href="#"', $contents);
            $this->assertStringNotContainsString('weakest link', $contents);
            $this->assertStringNotContainsString('good bye', $contents);
            $this->assertStringNotContainsString('Darn it', $contents);
            $this->assertStringNotContainsString("I've got nothing good to say", $contents);
            $this->assertStringNotContainsString('tumbleweed', $contents);
            $this->assertStringNotContainsString('fresh out', $contents);
            $this->assertStringNotContainsString("Don't be shy", $contents);
            $this->assertStringNotContainsString('🔥', $contents);
            $this->assertStringNotContainsString('🙉', $contents);
            $this->assertStringNotContainsString('🤟', $contents);
            $this->assertStringNotContainsString('✅', $contents);
            $this->assertStringNotContainsString('🤕', $contents);
            $this->assertStringNotContainsString('😰', $contents);
            $this->assertStringNotContainsString('🔎', $contents);
            $this->assertStringNotContainsString('😔', $contents);
            $this->assertStringNotContainsString('🕔', $contents);
            $this->assertStringNotContainsString('👎', $contents);
            $this->assertStringNotContainsString('👍', $contents);
        }

        $this->assertStringContainsString('disabled>Collaborator</Button>', $sidebar);
        $this->assertStringContainsString('disabled>Application pending</Button>', $sidebar);
        $this->assertStringContainsString('No collaborators have joined yet.', $sidebar);
        $this->assertStringContainsString('<h1 class="text-2xl font-semibold text-white">Ideas</h1>', $ideasIndex);
        $this->assertStringContainsString('No ideas matched', $ideasIndex);
        $this->assertStringContainsString('No pending applications.', $ideasManage);
        $this->assertStringContainsString('You have not shared any ideas yet.', $dashboard);
        $this->assertStringContainsString('This member has not added a bio yet.', $profile);
        $this->assertStringContainsString('type="button" variant="outline" disabled', $pagination);
        $this->assertStringContainsString('Collaborators have been invited.', $ideaController);
        $this->assertStringContainsString('has been removed from this idea.', $applicationController);
        $this->assertStringContainsString('GitHub account unlinked.', $socialController);
        $this->assertStringContainsString('Too many attempts', $errorPage);
        $this->assertStringContainsString('Reading and browsing still work', $errorPage);
    }

    public function test_new_tab_links_are_isolated_from_the_opening_window(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path())
        );

        foreach ($files as $file) {
            if (! $file instanceof \SplFileInfo || ! in_array($file->getExtension(), ['vue', 'php'], true)) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            $this->assertIsString($contents);

            preg_match_all('/<a\\b[^>]*target="_blank"[^>]*>/i', $contents, $matches);

            foreach ($matches[0] as $link) {
                $this->assertMatchesRegularExpression(
                    '/\\brel="[^"]*\\bnoopener\\b[^"]*\\bnoreferrer\\b[^"]*"/i',
                    $link,
                    "Expected {$file->getPathname()} to isolate new-tab link: {$link}"
                );
            }
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
