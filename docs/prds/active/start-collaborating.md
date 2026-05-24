# Start Collaborating PRD

## Problem

Collabbing helps people share ideas and apply to collaborate, but the current core journey leaves too much uncertainty between interest and action. A user can create an idea, another user can apply, and an owner can approve them, but both sides still have to infer what the idea needs, what the applicant should do first, who to contact, and how collaboration actually begins.

That gap is the product risk. If people feel unsure after applying or approving, Collabbing becomes an idea board instead of a place where collaboration starts.

## User Outcome

A person should be able to move from seeing an idea to starting useful collaboration without awkward guessing.

The ideal moment is:

> "I found an idea, understood how I could help, applied quickly, got accepted, and knew exactly where to go next."

## Product Principle

Remove uncertainty, not agency.

The product should not add heavy process, mandatory project-management setup, or job-application friction. It should make the next useful action obvious at each step while preserving a casual, low-barrier collaboration feel.

## Goals

- Make every created idea collaboration-ready enough for another person to understand how to help.
- Make the idea page answer "Can I join, what would I do, and who do I talk to?" without requiring deep reading.
- Make applying lightweight, specific, and fast.
- Make the post-application state clear for applicants and idea owners.
- Make approval produce an immediate getting-started moment for both sides.
- Improve the existing create -> apply -> approve -> collaborate flow before adding separate product modes.

## Non-Goals

- Do not build project management, task boards, kanban, sprint planning, or full team workspaces in this phase.
- Do not add social gamification, points, leaderboards, build rooms, or matchmaking as standalone features.
- Do not require owners to fully specify roles, tasks, or legal agreements before posting an idea.
- Do not make applications feel like job applications.
- Do not require external chat integrations before collaboration can begin.

## Primary Users

- **Idea owner:** shares an idea and wants the right people to understand how to help.
- **Potential collaborator:** finds an idea and wants to know whether they can contribute without committing blindly.
- **Accepted collaborator:** has been approved and needs a clear first step.
- **Supporter or lurker:** is interested but may not be ready to apply yet.

## Current Flow

The current product already has the right foundation:

- Idea creation captures title, tagline, communication preference, tags, repository name, summary, pitch content, and status.
- Idea pages show pitch content, collaborators, repository activity, supporters, comments, and application actions.
- Applications capture a markdown message.
- Owners can approve or decline applications from the manage page.
- Approved users become collaborators and can appear in dashboards and idea sidebars.

The underdeveloped part is not the existence of the flow. It is the clarity and continuity between each step.

## Proposed Flow

### 1. Create A Collaboration-Ready Idea

The idea form should help the owner answer the smallest useful set of collaboration questions:

- What are you trying to build?
- What stage is this in?
- What kind of help would be useful?
- What is the first thing someone could do?
- How should people contact or coordinate with you?
- Is there a repository or will one be created later?

The owner should be able to skip or defer optional details. If a field is empty, the interface should gracefully show "not decided yet" states rather than blocking publication.

### 2. Understand How To Get Involved

The idea page should include a clear "Start collaborating" panel that summarizes:

- Whether applications are open.
- The collaboration stage.
- Help wanted.
- First useful action.
- Communication preference.
- Repository status.
- Existing collaborators and pending state for the current user.

This panel should be scannable without replacing the full pitch.

### 3. Apply Quickly With Useful Intent

The application flow should ask for enough context to be useful without becoming a form wall:

- How do you want to help?
- What can you do first?
- Optional message or context.

The current markdown application body can remain, but the page should guide the applicant toward a practical answer. The goal is an application that helps the owner decide and helps the applicant clarify their own intent.

### 4. Make Waiting Clear

After applying, the applicant should see:

- Application pending state.
- Who owns the idea.
- Communication or comment path if available.
- What they can do while waiting, such as support the idea, comment, follow updates, or review the repository.

The owner should see:

- Applicant intent.
- Suggested actions: approve, ask a question, decline.
- The applicant profile and contact context.
- Whether repository access or communication setup may be needed after approval.

### 5. Turn Approval Into A Starting Point

Approving an applicant should create a clear next-step state:

- The applicant becomes a collaborator.
- Both sides see a "You are ready to start" moment.
- The idea page and dashboard highlight first action, contact path, and repository link if present.
- The owner is prompted to invite collaborators to the repository when applicable.

This should feel like the collaboration has started, not like an admin status changed.

## Requirements

### Idea Creation

- The idea create/edit experience must capture or derive collaboration guidance without blocking low-effort posting.
- Existing fields should be reused where they already fit.
- New fields should only be added when they remove meaningful ambiguity.
- The form should keep the main creation path short, with progressive or optional guidance for richer details.
- Draft behavior must continue to protect unsaved idea form work.

### Idea Page

- The idea page must show one clear primary collaboration action based on the viewer state:
  - Apply to collaborate.
  - Application pending.
  - Start collaborating.
  - Manage idea.
  - Sign in or register to apply.
- The idea page must answer what help is wanted and what happens next.
- Existing supporter, collaborator, repository, and comment surfaces should support the collaboration path rather than compete with it.

### Application Flow

- Applying should remain possible in under one minute.
- Applicants should be guided to describe practical contribution intent.
- The flow should preserve markdown support where long-form context is useful.
- Duplicate or invalid application states must remain protected by backend authorization and validation.

### Owner Review

- The manage page should make application review action-oriented.
- Owners should be able to understand applicant intent without parsing an unstructured essay.
- Approving should lead naturally into collaborator onboarding and repository invite actions.
- Declining should remain simple and should not require a long moderation workflow.

### Accepted Collaborator Experience

- Accepted collaborators should have a clear dashboard or idea-page path back to the collaboration.
- They should be able to see who to contact, what to do first, and where the repository is if available.
- Empty or undecided states must be honest and useful rather than dead ends.

## Data And API Notes

Potential new idea-level attributes:

- `collaboration_stage`
- `help_wanted`
- `first_contribution`
- `getting_started_notes`

Potential new application-level attributes:

- `contribution_type`
- `first_action`

These should be validated through request classes and passed through DTO-backed service and repository boundaries. The exact schema should be confirmed during implementation planning. If existing fields can cover part of the need cleanly, prefer reusing them over adding columns.

All timestamps should remain stored and retrieved in UTC.

## Security And Privacy

- Do not expose private contact details by default.
- Communication preferences should support safe public text, but owners should not be forced to publish personal email, phone, or private invite links.
- Markdown rendering must continue to use the existing sanitized rendering path.
- Application visibility should remain limited to the idea owner and authorized users.
- Repository invite actions must remain permission-checked.

## Acceptance Criteria

- A new idea can be created with enough guidance for a stranger to understand how to help.
- An existing idea can be edited to improve collaboration guidance.
- A signed-in non-owner can understand the next collaboration action from the idea page without reading every comment.
- A signed-in non-owner can apply with a specific contribution intent.
- An applicant can see that their application is pending and what to do next.
- An idea owner can review an application and understand what the applicant wants to contribute.
- Approving an application gives the new collaborator a clear getting-started path.
- Empty states for no repository, no first task, no collaborators, or undecided communication remain helpful.
- The flow works on mobile and desktop.
- The implementation does not introduce a standalone project-management surface.

## Suggested Implementation Slices

### Slice 1: PRD And Flow Audit

- Confirm final field names and route/view touchpoints.
- Identify which ambiguity can be solved with copy/layout versus schema.
- Document the exact viewer states for idea pages and applications.

### Slice 2: Idea Creation Guidance

- Improve the idea form around collaboration readiness.
- Add only the minimal schema needed for help wanted and first contribution guidance.
- Update validation, DTOs, services, repositories, factories, and feature tests.

### Slice 3: Start Collaborating Panel

- Add a prominent idea-page panel that adapts to owner, applicant, collaborator, guest, and unrelated signed-in user states.
- Ensure supporter, repository, collaborator, and comment controls remain secondary to the primary collaboration action.

### Slice 4: Application Intent

- Refine the application page so applicants state how they want to help and what they can do first.
- Update owner review cards to surface that intent.
- Preserve markdown context for longer messages.

### Slice 5: Accepted Collaborator Start State

- After approval, make the collaborator path clear on the idea page and dashboard.
- Connect repository invite prompts and communication notes into the accepted state.
- Add focused tests around approval and post-approval rendering.

## Verification Plan

- Run backend feature tests through the PHP Docker container.
- Run frontend tests and typecheck through the Node Docker container.
- Add or update feature tests for idea creation, application submission, owner review, approval, and authorization edge cases.
- Add or update frontend tests for idea form guidance, idea page viewer states, application form behavior, and manage page review cards.
- Browser-check the full flow on desktop and mobile:
  - owner creates idea
  - applicant applies
  - applicant sees pending state
  - owner approves
  - collaborator sees getting-started state

## Risks

- Adding too many fields could make idea creation feel heavier.
- Too much structure could make Collabbing feel like hiring software.
- Free-text communication fields could accidentally encourage unsafe public sharing of private contact details.
- A partial rollout could improve creation but still leave the approval-to-start gap unresolved.
- Existing idea cards and sidebars may become visually crowded if every collaboration signal is surfaced at once.

## Open Questions

- Should "help wanted" be structured options, free text, or both?
- Should first contribution guidance live on the idea only, or should owners define multiple optional contribution paths later?
- Should applicants choose a contribution type from a fixed list?
- Should accepted collaborators receive an in-app notification, email, or just a visible dashboard state in the first implementation?
- Should comments be available before applying, after applying, or only after being accepted?
- Should public communication preference allow links, or should private coordination be handled after approval?

## Links

- Current idea form: `resources/js/pages/Ideas/Form.vue`
- Current application form: `resources/js/pages/Ideas/Apply.vue`
- Current idea page: `resources/js/pages/Ideas/Show.vue`
- Current idea sidebar: `resources/js/components/ideas/IdeaSidebar.vue`
- Current owner manage page: `resources/js/pages/Ideas/Manage.vue`
