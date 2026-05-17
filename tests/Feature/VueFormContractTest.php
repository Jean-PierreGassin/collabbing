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
        $this->assertStringContainsString(':pattern="repositoryNamePattern"', $idea);
        $this->assertStringContainsString('@beforeinput="blockInvalidRepositoryNameInput"', $idea);
        $this->assertStringContainsString('@paste="pasteRepositoryName"', $idea);
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
        $this->assertStringContainsString(":role=\"toast.tone === 'error' ? 'alert' : 'status'\"", $contents);
        $this->assertStringContainsString(":aria-live=\"toast.tone === 'error' ? 'assertive' : 'polite'\"", $contents);
        $this->assertStringContainsString('inset-x-3 bottom-3', $contents);
        $this->assertStringContainsString('sm:max-w-md', $contents);
        $this->assertStringContainsString('sm:min-h-24', $contents);
        $this->assertStringContainsString('toasts.value = [toast];', $contents);
        $this->assertStringContainsString('fields need attention. Review the highlighted fields.', $contents);
        $this->assertStringContainsString('line-clamp-2', $contents);
        $this->assertStringContainsString('@mouseenter="pauseToast(toast)"', $contents);
    }

    public function test_shell_provides_page_transitions_and_default_seo_metadata(): void
    {
        $shell = file_get_contents(resource_path('js/components/layout/AppShell.vue'));
        $blade = file_get_contents(resource_path('views/app.blade.php'));

        $this->assertIsString($shell);
        $this->assertIsString($blade);
        $this->assertStringContainsString('<Transition name="page-fade" mode="out-in">', $shell);
        $this->assertStringContainsString('class="page-transition-panel"', $shell);
        $this->assertStringContainsString('<Head :title="seo.title">', $shell);
        $this->assertStringContainsString('head-key="description"', $shell);
        $this->assertStringContainsString('property="og:title"', $shell);
        $this->assertStringContainsString('name="twitter:card"', $shell);
        $this->assertStringContainsString('rel="canonical"', $shell);
        $this->assertStringContainsString('rel="icon" type="image/svg+xml" href="/favicon.svg"', $blade);
        $this->assertStringContainsString('rel="manifest" href="/site.webmanifest"', $blade);
        $this->assertStringContainsString('<title>Collabbing</title>', $blade);
        $this->assertStringContainsString('type="application/ld+json"', $blade);
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

    public function test_mobile_navigation_uses_compact_disclosure_controls(): void
    {
        $contents = file_get_contents(resource_path('js/components/layout/AppShell.vue'));

        $this->assertIsString($contents);
        $this->assertStringContainsString('const isMobileMenuOpen = ref(false);', $contents);
        $this->assertStringContainsString('const isMobileSearchOpen = ref(false);', $contents);
        $this->assertStringContainsString('class="size-11"', $contents);
        $this->assertStringContainsString(":aria-label=\"isMobileSearchOpen ? 'Close search' : 'Search ideas'\"", $contents);
        $this->assertStringContainsString(":aria-label=\"isMobileMenuOpen ? 'Close navigation menu' : 'Open navigation menu'\"", $contents);
        $this->assertStringContainsString('id="mobile-site-search"', $contents);
        $this->assertStringContainsString('id="mobile-navigation"', $contents);
        $this->assertStringContainsString('Mobile main navigation', $contents);
        $this->assertStringContainsString('Mobile workspace navigation', $contents);
        $this->assertStringContainsString('class="h-11 justify-start"', $contents);
        $this->assertStringContainsString('mobile-panel', file_get_contents(resource_path('css/app.css')));
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

    public function test_comment_sections_avoid_redundant_titles_and_labels(): void
    {
        $comments = file_get_contents(resource_path('js/components/comments/CommentList.vue'));
        $show = file_get_contents(resource_path('js/pages/Ideas/Show.vue'));

        $this->assertIsString($comments);
        $this->assertIsString($show);
        $this->assertStringContainsString('>Comments</h2>', $comments);
        $this->assertStringContainsString('>Add comment</h2>', $show);
        $this->assertStringContainsString('label="Comment"', $show);
        $this->assertStringNotContainsString('Collaborator Comments', $comments);
        $this->assertStringNotContainsString('Share your Comment', $show);
        $this->assertStringNotContainsString('label="Content"', $show);
    }

    public function test_profile_edit_flow_uses_inertia_navigation_for_transitions(): void
    {
        $show = file_get_contents(resource_path('js/pages/Users/Show.vue'));
        $form = file_get_contents(resource_path('js/pages/Users/Form.vue'));

        $this->assertIsString($show);
        $this->assertIsString($form);
        $this->assertStringContainsString("import { Link } from '@inertiajs/vue3';", $show);
        $this->assertStringContainsString(':as="Link"', $show);
        $this->assertStringContainsString("import { router } from '@inertiajs/vue3';", $form);
        $this->assertStringContainsString('@submit.prevent="submitProfile"', $form);
        $this->assertStringContainsString('router.post(props.user.routes.update', $form);
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
