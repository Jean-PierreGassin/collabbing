<?php

namespace Tests\Feature;

use Tests\TestCase;

class VueInteractionContractTest extends TestCase
{
    public function testCollaborationCopyUsesClearActionsAndStateInsteadOfPlaceholderLinks(): void
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
        $this->assertStringContainsString('{{ pitchToggleLabel }}', $ideaCard);
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
        $this->assertStringContainsString('let mobileOffset = 96;', $ideaShow);
        $this->assertStringContainsString('window.scrollTo({', $ideaShow);
        $this->assertStringContainsString('let scrollDelay = 0;', $ideaShow);
        $this->assertStringContainsString('scrollDelay = 280;', $ideaShow);
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
        $this->assertStringContainsString('headingLinkClass(heading)', $ideaShow);
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

    public function testCommentSectionsAvoidRedundantTitlesAndLabels(): void
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
        $this->assertStringContainsString('const labelClass = computed', file_get_contents(resource_path('js/components/forms/FormField.vue')));
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
        $this->assertStringContainsString('commentsPanelClass()', $comments);
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

    public function testCommentThreadsSupportInlineEditingAndProgressiveReplies(): void
    {
        $thread = file_get_contents(resource_path('js/components/comments/CommentThread.vue'));

        $this->assertIsString($thread);
        $this->assertStringContainsString('const isEditing = ref(false);', $thread);
        $this->assertStringContainsString('Save changes', $thread);
        $this->assertStringContainsString('method="PUT"', $thread);
        $this->assertStringContainsString('const areRepliesVisible = ref(false);', $thread);
        $this->assertStringContainsString('repliesToggleLabel(areRepliesVisible)', $thread);
    }

    public function testProfileEditFlowUsesInertiaNavigationForTransitions(): void
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

    public function testNewTabLinksAreIsolatedFromTheOpeningWindow(): void
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
}
