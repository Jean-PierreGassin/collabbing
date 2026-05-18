<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VueFormContractTest extends TestCase
{
    public function testPageComponentsIncludeAPageLevelHeading(): void
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
            $this->assertTrue(
                str_contains($contents, '<h1') || str_contains($contents, '<PageHeader'),
                "Expected {$page->getPathname()} to include a page-level h1 or shared page header."
            );
        }
    }

    #[DataProvider('vueFormFields')]
    public function testVueFormsIncludeExpectedBackendFields(string $path, array $fields): void
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

    public function testSharedFormFieldExposesAccessibleHelpAndErrorStateToControls(): void
    {
        $contents = file_get_contents(resource_path('js/components/forms/FormField.vue'));

        $this->assertIsString($contents);
        $this->assertStringContainsString(':described-by="describedBy"', $contents);
        $this->assertStringContainsString(':id="helpId"', $contents);
        $this->assertStringContainsString(':id="errorId"', $contents);
        $this->assertStringContainsString('role="alert"', $contents);
    }

    public function testAuthFormsIncludeBrowserAndAssistiveTechnologyContracts(): void
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
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $register);
        $this->assertStringContainsString("import { oldInputBoolean, oldInputString } from '@/lib/forms';", $login);
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $passwordEmail);
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $passwordReset);
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $profile);
        $this->assertStringContainsString(":value=\"oldInputString('username')\"", $register);
        $this->assertStringContainsString(":value=\"oldInputString('username')\"", $login);
        $this->assertStringContainsString(":checked=\"oldInputBoolean('remember')\"", $login);
    }

    public function testCollaborationFormsIncludeBrowserAndAssistiveTechnologyContracts(): void
    {
        $idea = file_get_contents(resource_path('js/pages/Ideas/Form.vue'));
        $comment = file_get_contents(resource_path('js/pages/Comments/Form.vue'));
        $inlineComment = file_get_contents(resource_path('js/components/comments/CommentComposer.vue'));
        $application = file_get_contents(resource_path('js/pages/Ideas/Apply.vue'));
        $profile = file_get_contents(resource_path('js/pages/Users/Form.vue'));

        foreach ([$idea, $comment, $inlineComment, $application, $profile] as $contents) {
            $this->assertIsString($contents);
            $this->assertStringContainsString(':aria-invalid="invalid || undefined"', $contents);
            $this->assertStringContainsString(':aria-describedby="describedBy"', $contents);
        }

        $this->assertStringContainsString('maxlength="100"', $idea);
        $this->assertStringContainsString('maxlength="50"', $idea);
        $this->assertStringContainsString('maxlength="240"', $idea);
        $this->assertStringContainsString('Plain text only. This appears on idea cards.', $idea);
        $this->assertStringContainsString(':pattern="repositoryNamePattern"', $idea);
        $this->assertStringContainsString('@beforeinput="blockInvalidRepositoryNameInput"', $idea);
        $this->assertStringContainsString('@paste="pasteRepositoryName"', $idea);
        $this->assertStringContainsString('maxlength="20000"', $idea);
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $idea);
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $comment);
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $inlineComment);
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $application);
        $this->assertStringContainsString(":value=\"oldInputString('title', idea?.title)\"", $idea);
        $this->assertStringContainsString("stripGeneratedTableOfContents(oldInputString('content', props.idea?.content))", $idea);
        $this->assertStringContainsString(":value=\"oldInputString('content', comment?.content)\"", $comment);
        $this->assertStringContainsString(":value=\"oldInputString('content')\"", $application);
        $this->assertStringContainsString('shouldUseOldContent', $inlineComment);
        $this->assertStringContainsString('insertMarkdownFiles', $idea);
        $this->assertStringContainsString('renderMarkdownPreview', $idea);
        $this->assertStringContainsString('MarkdownContent v-if="contentBody.trim()"', $idea);
        $this->assertStringContainsString('Preview table of contents', $idea);
        $this->assertStringContainsString('xl:grid-cols-[minmax(0,1fr)_16rem]', $idea);
        $this->assertStringContainsString('xl:self-start', $idea);
        $this->assertStringContainsString('class="pt-1"', $idea);
        $this->assertStringNotContainsString('border-t border-border pt-3', $idea);
        $this->assertStringContainsString('previewHeadings', $idea);
        $this->assertStringContainsString('Headings appear here.', $idea);
        $this->assertStringNotContainsString('xl:grid-cols-[minmax(0,1fr)_13rem]', $idea);
        $this->assertStringNotContainsString("'## Table of contents'", $idea);
        $this->assertStringNotContainsString('content.value = withGeneratedTableOfContents', $idea);
        $this->assertStringContainsString('accept=".md,.markdown,text/markdown,text/plain"', $idea);
        $this->assertStringContainsString('@drop.prevent="dropMarkdownFiles"', $idea);
        $this->assertStringNotContainsString('contentInput.value?.dispatchEvent', $idea);
        $this->assertStringContainsString('overflow-wrap: anywhere;', file_get_contents(resource_path('css/app.css')));
        $this->assertStringContainsString('white-space: pre-wrap;', file_get_contents(resource_path('css/app.css')));
        $this->assertStringContainsString('maxlength="1500"', $comment);
        $this->assertStringContainsString('maxlength="1500"', $inlineComment);
        $this->assertStringContainsString('maxlength="1500"', $application);
        $this->assertStringContainsString('maxlength="500"', $profile);
        $this->assertStringContainsString('form="github-revoke-form"', $profile);
        $this->assertStringContainsString('id="github-revoke-form"', $profile);
    }

    public function testFlashMessagesAreAnnouncedToAssistiveTechnology(): void
    {
        $contents = file_get_contents(resource_path('js/components/layout/FlashMessages.vue'));

        $this->assertIsString($contents);
        $this->assertStringContainsString(':role="toastRole(toast.tone)"', $contents);
        $this->assertStringContainsString(':aria-live="toastAriaLive(toast.tone)"', $contents);
        $this->assertStringContainsString('inset-x-3 bottom-3', $contents);
        $this->assertStringContainsString('sm:max-w-md', $contents);
        $this->assertStringContainsString('sm:min-h-24', $contents);
        $this->assertStringContainsString('toasts.value = [toast];', $contents);
        $this->assertStringContainsString('fields need attention. Review the highlighted fields.', $contents);
        $this->assertStringContainsString('line-clamp-2', $contents);
        $this->assertStringContainsString('@mouseenter="pauseToast(toast)"', $contents);
    }

    public function testShellProvidesPageTransitionsAndDefaultSeoMetadata(): void
    {
        $main = file_get_contents(resource_path('js/main.ts'));
        $navigationHistory = file_get_contents(resource_path('js/lib/navigationHistory.ts'));
        $shell = file_get_contents(resource_path('js/components/layout/AppShell.vue'));
        $breadcrumb = file_get_contents(resource_path('js/components/navigation/BreadcrumbBar.vue'));
        $blade = file_get_contents(resource_path('views/app.blade.php'));

        $this->assertIsString($main);
        $this->assertIsString($navigationHistory);
        $this->assertIsString($shell);
        $this->assertIsString($breadcrumb);
        $this->assertIsString($blade);
        $this->assertStringContainsString("import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue', { eager: true })", $main);
        $this->assertStringContainsString('startNavigationHistory(props.initialPage.url);', $main);
        $this->assertStringContainsString("parsed.searchParams.delete('comments');", $navigationHistory);
        $this->assertStringContainsString("const storageReturnKey = 'collabbing.navigation.returnUrl';", $navigationHistory);
        $this->assertStringContainsString('function navigationScope(url: string | null): string | null', $navigationHistory);
        $this->assertStringContainsString('if (! isSameNavigationScope(currentUrl.value, normalizedNextUrl))', $navigationHistory);
        $this->assertStringContainsString("router.on('navigate'", $navigationHistory);
        $this->assertStringContainsString("return 'Dashboard';", $navigationHistory);
        $this->assertStringNotContainsString('useContextualBack', $navigationHistory);
        $this->assertStringContainsString("import BreadcrumbBar from '@/components/navigation/BreadcrumbBar.vue';", $shell);
        $this->assertStringContainsString('<BreadcrumbBar />', $shell);
        $this->assertStringContainsString('aria-label="Breadcrumb"', $breadcrumb);
        $this->assertStringContainsString('bg-primary/[0.07]', $breadcrumb);
        $this->assertStringContainsString('text-primary/70', $breadcrumb);
        $this->assertStringNotContainsString('v-if="breadcrumbs.length > 1"', $breadcrumb);
        $this->assertStringContainsString("if (page.component === 'Dashboard')", $breadcrumb);
        $this->assertStringContainsString("if (page.component === 'Ideas/Index')", $breadcrumb);
        $this->assertStringContainsString("return [{ label: 'Collabbing' }];", $breadcrumb);
        $this->assertStringContainsString('label: `Idea - ${idea.titleDisplay}`', $breadcrumb);
        $this->assertStringContainsString('label: `Member - ${user.name}`', $breadcrumb);
        $this->assertStringContainsString("if (page.component === 'Contact')", $breadcrumb);
        $this->assertStringContainsString("return ideaCrumbs(idea, 'Manage');", $breadcrumb);
        $this->assertStringContainsString("return ideaCrumbs(idea, 'Edit');", $breadcrumb);
        $this->assertStringContainsString("return ideaCrumbs(idea, 'Apply');", $breadcrumb);
        $this->assertStringContainsString('aria-current', $breadcrumb);
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
        $this->assertStringContainsString("params.delete('comments');", $shell);
        $this->assertStringContainsString(':key="transitionKey"', $shell);
    }

    public function testAppShellProvidesAKeyboardBypassToMainContent(): void
    {
        $contents = file_get_contents(resource_path('js/components/layout/AppShell.vue'));

        $this->assertIsString($contents);
        $this->assertStringContainsString('href="#main-content"', $contents);
        $this->assertStringContainsString('Skip to main content', $contents);
        $this->assertStringContainsString('<main id="main-content" tabindex="-1"', $contents);
        $this->assertStringContainsString('focus:translate-y-0', $contents);
    }

    public function testUserAvatarsHoldSpaceAndFadeInAfterLoading(): void
    {
        $avatar = file_get_contents(resource_path('js/components/users/UserAvatar.vue'));
        $profile = file_get_contents(resource_path('js/pages/Users/Show.vue'));
        $members = file_get_contents(resource_path('js/pages/Users/Index.vue'));
        $sidebar = file_get_contents(resource_path('js/components/ideas/IdeaSidebar.vue'));

        $this->assertIsString($avatar);
        $this->assertIsString($profile);
        $this->assertIsString($members);
        $this->assertIsString($sidebar);
        $this->assertStringContainsString('bg-gradient-to-br from-secondary via-card to-background', $avatar);
        $this->assertStringContainsString('imageOpacityClass', $avatar);
        $this->assertStringContainsString('decoding="async"', $avatar);
        $this->assertStringContainsString('image.value?.complete', $avatar);
        $this->assertStringContainsString('UserAvatar', $profile);
        $this->assertStringContainsString('loading="eager"', $profile);
        $this->assertStringContainsString('UserAvatar', $members);
        $this->assertStringContainsString('UserAvatar', $sidebar);
        $this->assertStringNotContainsString('<img class="size-16', $profile);
        $this->assertStringNotContainsString('<img class="size-12', $members);
        $this->assertStringNotContainsString('<img', $sidebar);
    }

    public function testMobileNavigationUsesCompactDisclosureControls(): void
    {
        $contents = file_get_contents(resource_path('js/components/layout/AppShell.vue'));

        $this->assertIsString($contents);
        $this->assertStringContainsString('const isMobileMenuOpen = ref(false);', $contents);
        $this->assertStringContainsString('const isMobileSearchOpen = ref(false);', $contents);
        $this->assertStringContainsString('class="size-11"', $contents);
        $this->assertStringContainsString(':aria-label="mobileSearchLabel"', $contents);
        $this->assertStringContainsString(':aria-label="mobileMenuLabel"', $contents);
        $this->assertStringContainsString('id="mobile-site-search"', $contents);
        $this->assertStringContainsString('id="mobile-navigation"', $contents);
        $this->assertStringContainsString('Mobile main navigation', $contents);
        $this->assertStringContainsString('Mobile workspace navigation', $contents);
        $this->assertStringContainsString('class="h-11 justify-start"', $contents);
        $this->assertStringContainsString('mobile-panel', file_get_contents(resource_path('css/app.css')));
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
                ['title', 'summary', 'repository_name', 'communication', 'content', 'status'],
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
