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
            $this->assertTrue(
                str_contains($contents, '<h1') || str_contains($contents, '<PageHeader'),
                "Expected {$page->getPathname()} to include a page-level h1 or shared page header."
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
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $register);
        $this->assertStringContainsString("import { oldInputBoolean, oldInputString } from '@/lib/forms';", $login);
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $passwordEmail);
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $passwordReset);
        $this->assertStringContainsString("import { oldInputString } from '@/lib/forms';", $profile);
        $this->assertStringContainsString(":value=\"oldInputString('username')\"", $register);
        $this->assertStringContainsString(":value=\"oldInputString('username')\"", $login);
        $this->assertStringContainsString(":checked=\"oldInputBoolean('remember')\"", $login);
    }

    public function test_collaboration_forms_include_browser_and_assistive_technology_contracts(): void
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

    public function test_app_shell_provides_a_keyboard_bypass_to_main_content(): void
    {
        $contents = file_get_contents(resource_path('js/components/layout/AppShell.vue'));

        $this->assertIsString($contents);
        $this->assertStringContainsString('href="#main-content"', $contents);
        $this->assertStringContainsString('Skip to main content', $contents);
        $this->assertStringContainsString('<main id="main-content" tabindex="-1"', $contents);
        $this->assertStringContainsString('focus:translate-y-0', $contents);
    }

    public function test_user_avatars_hold_space_and_fade_in_after_loading(): void
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
        $this->assertStringContainsString("isLoaded ? 'opacity-100' : 'opacity-0'", $avatar);
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
        $ideaCard = file_get_contents(resource_path('js/components/ideas/IdeaCard.vue'));
        $pageHeader = file_get_contents(resource_path('js/components/navigation/PageHeader.vue'));
        $home = file_get_contents(resource_path('js/pages/Home.vue'));
        $contact = file_get_contents(resource_path('js/pages/Contact.vue'));
        $ideaShow = file_get_contents(resource_path('js/pages/Ideas/Show.vue'));
        $ideaForm = file_get_contents(resource_path('js/pages/Ideas/Form.vue'));
        $ideaApply = file_get_contents(resource_path('js/pages/Ideas/Apply.vue'));
        $ideasIndex = file_get_contents(resource_path('js/pages/Ideas/Index.vue'));
        $ideasManage = file_get_contents(resource_path('js/pages/Ideas/Manage.vue'));
        $dashboard = file_get_contents(resource_path('js/pages/Dashboard.vue'));
        $profile = file_get_contents(resource_path('js/pages/Users/Show.vue'));
        $pagination = file_get_contents(resource_path('js/components/pagination/PaginationLinks.vue'));
        $appCss = file_get_contents(resource_path('css/app.css'));
        $shell = file_get_contents(resource_path('js/components/layout/AppShell.vue'));
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
        $this->assertStringContainsString('<h1 class="text-2xl font-semibold leading-tight text-white">{{ title }}</h1>', $pageHeader);
        $this->assertStringNotContainsString('aria-label="Page navigation"', $pageHeader);
        $this->assertStringNotContainsString('useContextualBack', $pageHeader);
        $this->assertStringNotContainsString('Back to', $pageHeader);
        $this->assertStringNotContainsString('border-b border-border pb-5', $pageHeader);
        $this->assertStringNotContainsString('bg-card/80', $pageHeader);
        $this->assertStringContainsString(':aria-label="`Open ${idea.titleDisplay}`"', $ideaCard);
        $this->assertStringContainsString('hover:border-primary/35', $ideaCard);
        $this->assertStringContainsString("variant?: 'default' | 'compact';", $ideaCard);
        $this->assertStringContainsString("const isCompact = computed(() => props.variant === 'compact' && ! props.single);", $ideaCard);
        $this->assertStringContainsString('min-h-[18rem]', $ideaCard);
        $this->assertStringContainsString('v-if="!single && (idea.can.update || idea.repository)"', $ideaCard);
        $this->assertStringContainsString('{{ idea.summary }}', $ideaCard);
        $this->assertStringContainsString('[overflow-wrap:anywhere]', $ideaCard);
        $this->assertStringContainsString('aria-label="Idea summary"', $ideaCard);
        $this->assertStringNotContainsString('<h2 class="text-sm font-semibold text-white">Pitch</h2>', $ideaCard);
        $this->assertStringNotContainsString('In-depth overview', $ideaCard);
        $this->assertStringNotContainsString('Markdown pitch and implementation detail.', $ideaCard);
        $this->assertStringContainsString('v-if="!single"', $ideaCard);
        $this->assertStringContainsString('justify-start gap-2 border-t border-border bg-background/12', $ideaCard);
        $this->assertStringContainsString('<span class="text-border" aria-hidden="true">/</span>', $ideaCard);
        $this->assertStringContainsString("{{ isDescriptionExpanded ? 'Hide Pitch' : 'Show Pitch' }}", $ideaCard);
        $this->assertStringContainsString(':aria-controls="descriptionId"', $ideaCard);
        $this->assertStringContainsString('pitchExpanded?: boolean;', $ideaCard);
        $this->assertStringContainsString("'update:pitchExpanded': [value: boolean];", $ideaCard);
        $this->assertStringContainsString('@before-enter="beforeDescriptionEnter"', $ideaCard);
        $this->assertStringContainsString('@enter="enterDescription"', $ideaCard);
        $this->assertStringContainsString('@leave="leaveDescription"', $ideaCard);
        $this->assertStringContainsString('finishPanelTransition(panel, \'height\', done', $ideaCard);
        $this->assertStringContainsString('description-reveal-panel', $ideaCard);
        $this->assertStringContainsString('<slot name="pitch-toc" />', $ideaCard);
        $this->assertStringContainsString('class="min-h-0 min-w-0 overflow-hidden border-t border-border pt-4"', $ideaCard);
        $this->assertStringNotContainsString('rounded-md border border-border bg-background/35 p-4', $ideaCard);
        $this->assertStringContainsString('v-if="isDescriptionExpanded"', $ideaCard);
        $this->assertStringContainsString('supportersLabel', $ideaCard);
        $this->assertStringContainsString('collaboratorsLabel', $ideaCard);
        $this->assertStringContainsString('class="ml-auto"', $ideaCard);
        $this->assertStringNotContainsString(':href="idea.routes.edit"', $ideaCard);
        $this->assertStringNotContainsString('line-clamp-3', $ideaCard);
        $this->assertStringNotContainsString('View idea', $ideaCard);
        $this->assertStringContainsString('Search your dashboard ideas', $dashboard);
        $this->assertStringContainsString('name="search"', $dashboard);
        $this->assertStringContainsString('id="dashboard-search"', $dashboard);
        $this->assertStringContainsString('role="search"', $dashboard);
        $this->assertStringContainsString('class="h-10 w-full rounded-md border border-input bg-background pl-9 pr-20 text-sm outline-none transition-colors placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-ring/40"', $dashboard);
        $this->assertStringContainsString('md:grid-cols-[minmax(0,1fr)_minmax(16rem,22rem)_minmax(0,1fr)]', $dashboard);
        $this->assertStringContainsString('class="relative w-full md:justify-self-center"', $dashboard);
        $this->assertStringContainsString('class="justify-self-center md:justify-self-end"', $dashboard);
        $this->assertStringContainsString(':value="keyword ?? \'\'"', $dashboard);
        $this->assertStringContainsString(':ideas="ideas.items"', $dashboard);
        $this->assertStringContainsString(':paginator="ideas"', $dashboard);
        $this->assertStringContainsString(':ideas="collaborations.items"', $dashboard);
        $this->assertStringContainsString(':paginator="collaborations"', $dashboard);
        $this->assertStringNotContainsString('variant="compact"', $dashboard);
        $this->assertStringContainsString('Idea - {{ idea.titleDisplay }}', $ideaShow);
        $this->assertStringContainsString('class="flex shrink-0 flex-wrap justify-end gap-2 sm:pt-0.5"', $ideaShow);
        $this->assertStringContainsString('<GitBranch class="size-4"', $ideaShow);
        $this->assertStringContainsString('<Pencil class="size-4"', $ideaShow);
        $this->assertStringContainsString('v-model:pitch-expanded="isPitchExpanded"', $ideaShow);
        $this->assertStringContainsString('await nextTick();', $ideaShow);
        $this->assertStringContainsString('isMobileTocOpen.value = true;', $ideaShow);
        $this->assertStringContainsString('lockPitchNavigation(anchor);', $ideaShow);
        $this->assertStringContainsString('if (isPitchNavigationLocked)', $ideaShow);
        $this->assertStringContainsString('pitchNavigationTimer = window.setTimeout', $ideaShow);
        $this->assertStringContainsString("document.getElementById('mobile-pitch-contents')", $ideaShow);
        $this->assertStringContainsString("window.matchMedia('(max-width: 1699px)').matches", $ideaShow);
        $this->assertStringContainsString(': 96;', $ideaShow);
        $this->assertStringContainsString('window.scrollTo({', $ideaShow);
        $this->assertStringContainsString('shouldWaitForExpansion ? 280 : 0', $ideaShow);
        $this->assertStringContainsString('visiblePitchHeadings', $ideaShow);
        $this->assertStringContainsString('activePathAnchors', $ideaShow);
        $this->assertStringContainsString('new IntersectionObserver', $ideaShow);
        $this->assertStringContainsString('rootMargin: \'-18% 0px -65% 0px\'', $ideaShow);
        $this->assertStringContainsString('const isPitchContentInView = ref(false);', $ideaShow);
        $this->assertStringContainsString('const pitchDescriptionId = computed(() => `idea-${props.idea.id}-description`);', $ideaShow);
        $this->assertStringContainsString('const shouldShowMobileToc = computed(() => isPitchExpanded.value && pitchHeadings.value.length > 0 && isPitchContentInView.value);', $ideaShow);
        $this->assertStringContainsString('function updatePitchContentVisibility(): void', $ideaShow);
        $this->assertStringContainsString('const readingOffset = 96;', $ideaShow);
        $this->assertStringContainsString('pitchBounds.top <= readingOffset && pitchBounds.bottom > readingOffset', $ideaShow);
        $this->assertStringContainsString('function observePitchContent(): void', $ideaShow);
        $this->assertStringContainsString("window.addEventListener('scroll', updatePitchContentVisibility, { passive: true });", $ideaShow);
        $this->assertStringContainsString("window.removeEventListener('scroll', updatePitchContentVisibility);", $ideaShow);
        $this->assertStringContainsString('rootMargin: \'-64px 0px -22% 0px\'', $ideaShow);
        $this->assertStringContainsString('activePitchAnchor.value = anchor;', $ideaShow);
        $this->assertStringContainsString('relative flex flex-col gap-4', $ideaShow);
        $this->assertStringContainsString('<Teleport to="body">', $ideaShow);
        $this->assertStringContainsString('id="mobile-pitch-contents"', $ideaShow);
        $this->assertStringContainsString('v-if="shouldShowMobileToc"', $ideaShow);
        $this->assertStringContainsString('class="fixed inset-x-4 top-3 z-[70] rounded-2xl border border-border bg-background/90 px-3 py-2 shadow-lg shadow-background/35 backdrop-blur min-[1700px]:hidden"', $ideaShow);
        $this->assertStringNotContainsString("isMobileTocOpen ? 'rounded-2xl' : 'rounded-full'", $ideaShow);
        $this->assertStringContainsString('class="mx-auto flex max-w-2xl flex-col"', $ideaShow);
        $this->assertStringContainsString(':aria-expanded="isMobileTocOpen"', $ideaShow);
        $this->assertStringContainsString('@click="isMobileTocOpen = !isMobileTocOpen"', $ideaShow);
        $this->assertStringContainsString('<Transition name="toc-mobile">', $ideaShow);
        $this->assertStringContainsString('class="mt-2 max-h-[min(18rem,55dvh)] overflow-y-auto overscroll-contain border-l border-border py-2 text-sm"', $ideaShow);
        $this->assertStringContainsString('<Transition name="toc-float">', $ideaShow);
        $this->assertStringContainsString('v-if="isPitchExpanded && pitchHeadings.length > 0"', $ideaShow);
        $this->assertStringContainsString('hidden min-[1700px]:absolute min-[1700px]:inset-y-0 min-[1700px]:right-full min-[1700px]:mr-4 min-[1700px]:block min-[1700px]:w-56', $ideaShow);
        $this->assertStringContainsString('class="sticky top-24 p-2"', $ideaShow);
        $this->assertStringContainsString('class="flex flex-col gap-1 border-l border-border text-sm"', $ideaShow);
        $this->assertStringContainsString(':style="{ paddingLeft: `${0.75 + headingDepth(heading) * 0.9}rem` }"', $ideaShow);
        $this->assertStringContainsString('border-l-2 py-1.5 pr-2', $ideaShow);
        $this->assertStringContainsString('isActiveHeading(heading) ? \'border-primary text-primary\'', $ideaShow);
        $this->assertStringNotContainsString('fixed inset-x-0 top-0', $ideaShow);
        $this->assertStringNotContainsString('rounded-b-lg border-x border-b border-border bg-card/95', $ideaShow);
        $this->assertStringNotContainsString('rounded-lg border border-border bg-card/90', $ideaShow);
        $this->assertStringNotContainsString('rounded-md border border-border bg-background/35 p-2 text-sm', $ideaShow);
        $this->assertStringContainsString('Pitch table of contents', $ideaShow);
        $this->assertStringContainsString('@click.prevent="openPitchHeading(heading.anchor)"', $ideaShow);
        $this->assertStringNotContainsString('xl:sticky', $ideaShow);
        $this->assertStringNotContainsString('Pitch table of contents', $sidebar);
        $this->assertStringNotContainsString('pitchHeadings?: MarkdownHeading[];', $sidebar);
        $this->assertStringNotContainsString('Pitch table of contents', $ideaCard);
        $this->assertStringNotContainsString('show-actions', $ideaShow);
        $this->assertStringNotContainsString('back-label', $ideaShow);
        $this->assertStringNotContainsString('contextual-back', $ideaShow);
        $this->assertStringNotContainsString('back-label', $ideaForm);
        $this->assertStringNotContainsString('contextual-back', $ideaForm);
        $this->assertStringNotContainsString('back-label', $ideaApply);
        $this->assertStringNotContainsString('contextual-back', $ideaApply);
        $this->assertStringContainsString('<h1 class="sr-only">Ideas</h1>', $ideasIndex);
        $this->assertStringContainsString('<h1 class="sr-only">Dashboard</h1>', $dashboard);
        $this->assertStringNotContainsString('<h1 class="text-2xl font-semibold text-white">Dashboard</h1>', $dashboard);
        $this->assertStringNotContainsString('<h1 class="text-3xl font-semibold text-white">Ideas</h1>', $ideasIndex);
        $this->assertStringContainsString('const trendingIdeaIds = computed', $ideasIndex);
        $this->assertStringContainsString('const recentIdeas = computed', $ideasIndex);
        $this->assertStringContainsString('No ideas matched', $ideasIndex);
        $this->assertStringNotContainsString('back-label', $ideasManage);
        $this->assertStringNotContainsString('contextual-back', $ideasManage);
        $this->assertStringNotContainsString('View Overview', $ideasManage);
        $this->assertStringContainsString('<Pencil class="size-4"', $ideasManage);
        $this->assertStringContainsString('<Transition name="content-fade" mode="out-in">', $ideasManage);
        $this->assertStringContainsString('key="applications"', $ideasManage);
        $this->assertStringContainsString('key="collaborators"', $ideasManage);
        $this->assertStringContainsString('class="flex flex-wrap justify-end gap-2"', $ideasManage);
        $this->assertStringNotContainsString('showActions', $sidebar);
        $this->assertStringNotContainsString('class="flex flex-wrap justify-end gap-2"', $sidebar);
        $this->assertStringNotContainsString('Idea actions', $sidebar);
        $this->assertStringNotContainsString('<Pencil class="size-4"', $sidebar);
        $this->assertStringContainsString('applications.items.length', $ideasManage);
        $this->assertStringContainsString('collaborators.items.length', $ideasManage);
        $this->assertStringContainsString(':paginator="applications"', $ideasManage);
        $this->assertStringContainsString(':paginator="collaborators"', $ideasManage);
        $this->assertStringContainsString('No pending applications.', $ideasManage);
        $this->assertStringContainsString('You have not shared any ideas yet.', $dashboard);
        $this->assertStringContainsString('This member has not added a bio yet.', $profile);
        $this->assertStringContainsString('type="button"', $pagination);
        $this->assertStringContainsString('variant="outline"', $pagination);
        $this->assertStringContainsString('disabled', $pagination);
        $this->assertStringContainsString('w-[100dvw] max-w-[100dvw]', $home);
        $this->assertStringNotContainsString('w-screen', $home);
        $this->assertStringContainsString('overflow-x: clip;', $appCss);
        $this->assertStringContainsString('.content-fade-enter-active', $appCss);
        $this->assertStringContainsString('.description-reveal-panel', $appCss);
        $this->assertStringContainsString('.toc-float-enter-active', $appCss);
        $this->assertStringContainsString('.toc-mobile-enter-active', $appCss);
        $this->assertStringContainsString('.toc-item-enter-active', $appCss);
        $this->assertStringContainsString('max-height 180ms ease', $appCss);
        $this->assertStringContainsString('max-height 220ms ease', $appCss);
        $this->assertStringContainsString('max-height: min(18rem, 55dvh);', $appCss);
        $this->assertStringContainsString('max-height: 2.75rem;', $appCss);
        $this->assertStringContainsString('max-height: 0;', $appCss);
        $this->assertStringContainsString('scroll-margin-top: 6rem;', $appCss);
        $this->assertStringNotContainsString('.toc-scroll-panel', $appCss);
        $this->assertStringNotContainsString('.description-reveal-enter-active', $appCss);
        $this->assertStringContainsString(':href="session.routes.contact"', $shell);
        $this->assertStringNotContainsString('subject=Collabbing Feedback">Contact', $shell);
        $this->assertStringContainsString('subject=Collabbing Enquiry', $contact);
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
        $composer = file_get_contents(resource_path('js/components/comments/CommentComposer.vue'));

        $this->assertIsString($comments);
        $this->assertIsString($show);
        $this->assertIsString($composer);
        $this->assertStringContainsString('>Comments</h2>', $comments);
        $this->assertStringContainsString('label="Comment"', $comments);
        $this->assertStringContainsString('hide-label', $comments);
        $this->assertStringContainsString("label: 'Comment'", $composer);
        $this->assertStringContainsString(':hide-label="hideLabel"', $composer);
        $this->assertStringContainsString("hideLabel ? 'sr-only'", file_get_contents(resource_path('js/components/forms/FormField.vue')));
        $this->assertStringContainsString('Markdown and @mentions are supported.', $composer);
        $this->assertStringContainsString('Press Tab to insert the first mention.', $composer);
        $this->assertStringNotContainsString('Add comment', $comments);
        $this->assertStringNotContainsString('Latest first', $comments);
        $this->assertStringNotContainsString('overflow-y-auto', $comments);
        $this->assertStringContainsString(':only="[\'comments\']"', $comments);
        $this->assertStringContainsString('placement="toolbar"', $comments);
        $this->assertStringContainsString('label="Comment pages"', $comments);
        $this->assertStringNotContainsString('border-t border-border px-6 py-4', $comments);
        $this->assertStringContainsString('name="page-fade" mode="out-in"', $comments);
        $this->assertStringContainsString(':key="comments.currentPage"', $comments);
        $this->assertStringContainsString("comments.items.length > 0 ? 'min-h-[34rem]' : undefined", $comments);
        $pagination = file_get_contents(resource_path('js/components/pagination/PaginationLinks.vue'));
        $this->assertStringContainsString('preserve-state', $pagination);
        $this->assertStringContainsString("router.on('finish'", $pagination);
        $this->assertStringContainsString('placement === \'toolbar\'', $pagination);
        $this->assertStringContainsString('event.defaultPrevented || event.button !== 0', $pagination);
        $this->assertStringContainsString('isPaging.value = false;', $pagination);
        $this->assertStringNotContainsString('Collaborator Comments', $comments);
        $this->assertStringNotContainsString('Share your Comment', $show);
        $this->assertStringNotContainsString('label="Content"', $show);
    }

    public function test_comment_threads_support_inline_editing_and_progressive_replies(): void
    {
        $thread = file_get_contents(resource_path('js/components/comments/CommentThread.vue'));

        $this->assertIsString($thread);
        $this->assertStringContainsString('const isEditing = ref(false);', $thread);
        $this->assertStringContainsString('Save changes', $thread);
        $this->assertStringContainsString('method="PUT"', $thread);
        $this->assertStringContainsString('const areRepliesVisible = ref(false);', $thread);
        $this->assertStringContainsString("'Show' }} {{ comment.replies.length.toLocaleString()", $thread);
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
        $markdown = file_get_contents(resource_path('js/components/typography/MarkdownContent.vue'));

        $this->assertIsString($markdown);
        $this->assertStringContainsString('link.origin === window.location.origin', $markdown);
        $this->assertStringContainsString("link.target = '_blank';", $markdown);
        $this->assertStringContainsString("link.rel = 'noopener noreferrer';", $markdown);

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
