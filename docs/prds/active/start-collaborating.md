# Start Collaborating PRD

## Problem

Collabbing helps people share ideas and apply to collaborate, but the current core journey leaves too much uncertainty between interest and action. A user can create an idea, another user can apply, and an owner can approve them, but both sides still have to infer what the idea needs, what the applicant should do first, who to contact, and how collaboration actually begins.

That gap is the product risk. If people feel unsure after applying or approving, Collabbing becomes an idea board instead of a place where collaboration starts.

## Product Outcome

A person should be able to move from seeing an idea to starting useful collaboration without awkward guessing.

The ideal moment is:

> "I found an idea, understood how I could help, applied quickly, got accepted, and knew exactly where to go next."

The north star for this feature family is **time to first accepted collaborator**. MVP does not add analytics instrumentation, but implementation decisions should optimize for reducing that time.

## Product Principles

- Remove uncertainty, not agency.
- Low friction wins unless there is a clear privacy or security risk.
- Collaboration structure should feel like guidance, not hiring software.
- The product should improve the existing create -> apply -> approve -> collaborate loop before introducing separate product modes.
- When schema/data correctness and visible UX compete, build vertical thin slices first so one field or flow works end-to-end before expanding.

## Goals

- Make every created idea collaboration-ready enough for another person to understand how to help.
- Make the idea page answer "Can I join, what would I do, and who do I talk to?" without requiring deep reading.
- Make applying lightweight, specific, and fast.
- Make waiting, review, approval, decline, withdrawal, leaving, and removal states clear.
- Make approval produce an immediate getting-started moment for both sides.
- Use support and comments as low-friction interest paths without adding a separate follow feature.
- Keep the flow simple enough that people can collaborate without feeling blocked by process.

## Non-Goals

- Do not build project management, task boards, kanban, sprint planning, or full team workspaces in this phase.
- Do not add social gamification, points, leaderboards, build rooms, or matchmaking as standalone features.
- Do not add owner invites that bypass applications.
- Do not add moderation/reporting workflows in MVP.
- Do not add product analytics or an analytics dashboard in MVP.
- Do not add application attachments or file uploads.
- Do not add read receipts, full seen timestamps, direct-message surfaces, or collaborator broadcast messaging.
- Do not require owners to fully specify roles, tasks, legal agreements, repositories, or private start notes before posting an idea.

## Primary Users

- **Idea owner:** shares an idea and wants the right people to understand how to help.
- **Potential collaborator:** finds an idea and wants to know whether they can contribute without committing blindly.
- **Applicant:** has applied and wants to know what happens next.
- **Accepted collaborator:** has been approved and needs a clear first step.
- **Supporter or lurker:** is interested but may not be ready to apply yet.
- **Guest:** can read public collaboration guidance and is invited to sign in or register to apply/comment.

## Current Foundation

The product already has the right foundation:

- Idea creation captures title, tagline, communication preference, tags, repository name, summary, pitch content, and status.
- Idea pages show pitch content, collaborators, repository activity, supporters, comments, and application actions.
- Applications capture a markdown message.
- Owners can approve or decline applications from the manage page.
- Approved applications currently act as collaborator records.
- Approved users appear in dashboards and idea sidebars.

The underdeveloped part is not the existence of the flow. It is the clarity and continuity between each step.

## Core Flow

### 1. Create A Collaboration-Ready Idea

Idea creation uses a **two-step flow**:

1. **Idea basics**
2. **Collaboration setup**

Users must reach step 2 before publishing, but step 2 fields can be set to "not sure yet" or left in honest undecided states where appropriate. Creation has no final review screen; publishing happens from step 2.

Drafts persist across both creation steps. A simple step indicator should orient the user.

Repository name belongs in collaboration setup, not idea basics.

Editing an existing idea uses one form with clear sections rather than the two-step creation flow.

### 2. Understand How To Get Involved

The idea detail page includes a **Start Collaborating** panel at the top of the sidebar, beside the pitch. The pitch remains the main content, while the collaboration path stays visible.

The panel summarizes:

- Collaboration stage.
- Help wanted.
- First contribution prompt.
- Applications open/closed state.
- Public communication style.
- Repository availability.
- Current viewer state and primary action.
- Readiness badges.

Guests can see the Start Collaborating panel and all public collaboration guidance. Guests get register/sign-in CTAs for actions that require authentication.

### 3. Apply Quickly With Useful Intent

Applications capture:

- Contribution type.
- First action the applicant can take.
- Optional message/context with sanitized markdown.

Applicants choose from the full contribution type set, with owner-selected help areas visually recommended. Applying should remain possible in under one minute.

There is one active application per user per idea. Pending applications can be edited. Users can reapply after decline or withdrawal, but each application attempt has its own history.

### 4. Clarify Privately While Pending

Each application has a private application thread visible only to the idea owner and applicant. There is no separate thread model; messages belong directly to the application.

The thread is used for clarification before approval or decline. It becomes read-only after a final application decision or collaboration ending state.

### 5. Make Waiting Useful

After applying, the applicant sees a helpful pending state:

- Pending status.
- Link back to the idea.
- Application thread.
- Support action.
- Public comments.
- Repository link if available.
- Any public first contribution guidance.

The applicant should not see private contact details or accepted-collaborator notes until approved.

### 6. Review Applications

The owner manage page shows application cards that surface contribution type, first action, optional message, applicant profile context, application thread state, and decision actions.

Owners can:

- Approve.
- Decline with optional private reason through a confirm step.
- Ask questions in the private application thread.
- Review pending applications from dashboard/manage surfaces.

No owner-only labels, private notes, or hiring-style candidate management are included in MVP.

### 7. Turn Approval Into A Starting Point

Approval turns the applicant into an active collaborator through the existing approved application record.

If shared private getting-started notes exist, approval stays fast. If shared notes are empty, approval shows missing-guidance warning and an optional per-collaborator approval note field.

Approving should not require repository setup or repository invitation. When the idea has a connected repository, the owner is prompted to invite the collaborator after approval.

Accepted collaborators see:

- Shared private getting-started notes if present.
- Optional per-collaborator approval note if present.
- Public first contribution prompt.
- Public communication style.
- Repository link if present.
- Honest empty states if private guidance is missing.

### 8. Exit Collaboration Cleanly

Pending applicants can withdraw through a confirm step with no reason field. The owner is notified.

Accepted collaborators can leave through a confirm step with optional private reason to the owner. The owner is notified.

Owners can remove collaborators through a confirm step with optional private reason. The removed collaborator is notified.

When a collaborator leaves or is removed and the idea has a connected repository, owner-facing email and in-app prompts remind the owner to review repository access. Repository access is not removed automatically in MVP.

## Collaboration Setup

### Collaboration Stage

Use casual fixed stages:

- Rough idea
- Needs shaping
- Ready to build
- Actively building
- Live

Stage is informational only. Applications open/closed is controlled separately.

### Help Wanted

Help wanted uses structured selected areas plus short free text.

MVP help areas:

- Frontend
- Backend
- Design
- Product
- Testing
- DevOps
- Writing
- Research
- Feedback
- Marketing
- Anything

The "Anything" concept uses context-specific labels:

- Owner label: **Open to anything**
- Applicant label: **I can help with anything**

Selected help areas guide applications but do not block applicants from choosing another contribution type.

Help wanted free text is plain text only and should be short/scannable.

### First Contribution

MVP supports a single public first contribution prompt, designed so multiple starter paths can be added later.

The first contribution prompt is always public and supports links plus line breaks only. It should not become a long rich task spec.

### Applications Open/Closed

Ideas have an applications open/closed toggle with an optional public note.

- Closed applications block new applications.
- Closed applications do not affect existing pending applications.
- Closed applications do not close comments.
- Closed applications do not close support.
- Applications stay open after approval unless the owner manually closes them.
- The open/closed note is plain text only.

### Communication Style

Public communication style uses structured options plus optional short text. It describes coordination preference only; it does not expose direct contact details before acceptance.

MVP communication style options:

- GitHub
- Discord
- Slack
- Email
- Calls
- Not decided yet

For Email, the public surface only shows "Email". Actual email addresses or contact details belong in private accepted-collaborator notes.

### Private Getting-Started Notes

Owners can write shared private getting-started notes for accepted collaborators. These notes:

- Are visible only to active accepted collaborators and the owner.
- Support sanitized markdown.
- Are owner-editable only.
- Show a simple updated timestamp.
- Do not have version history in MVP.
- Can be updated without notification, or with owner-selected collaborator notification.

MVP also supports an optional per-collaborator approval note shown to that collaborator after approval. This note supports links plus line breaks only.

Left or removed collaborators lose access to shared private getting-started notes.

## Readiness Surfaces

### Owner Readiness Checklist

The owner manage page and creation step 2 show a factual Collaboration readiness checklist. During creation step 2, the checklist appears as a side panel on desktop and below fields on mobile.

The checklist is owner-only/manage-only and uses action wording:

- Set where the idea is at
- Choose the help you want
- Add a first thing someone can do
- Choose how you prefer to coordinate
- Decide whether applications are open
- Write private start notes for accepted collaborators
- Create or connect a repository

Completed and missing checklist items are both visible. Only missing items get direct action links where possible.

No "reply to recent questions" owner checklist item is included.

### Public Readiness Badges

The Start Collaborating panel uses factual badge labels with softer helper text where helpful.

MVP public badges:

- Applications open
- First step listed
- Repo available
- Start notes ready

Everyone can see that start notes are ready, but only accepted collaborators can see the note contents.

Do not include owner activity/comment response badges in MVP.

## Idea Cards And Public Visibility

Idea cards show:

- Up to three selected help areas.
- Collaboration stage.
- Application availability only when applications are closed.
- A small first-contribution badge when a first contribution exists, without showing the prompt content.

Public idea pages show:

- Help wanted.
- First contribution.
- Collaboration stage.
- Applications open/closed state and note.
- Public communication style.
- Public comments.
- Active accepted collaborators.
- Supporter information already available in the product.

Public idea pages do not show:

- Pending application counts.
- Private contact details.
- Private getting-started notes.
- Removed or left collaborators.

Guests can read public comments. Signed-in users can comment before applying or being accepted.

Owners see pending application counts on dashboard/manage surfaces. Pending counts are owner-only in MVP.

## Support

Support remains separate data, but it becomes a lightweight interest action in the collaboration flow.

Examples:

- "Not ready to apply? Support this idea."
- "While you wait, you can support or comment."

Support acts as the follow-ish/interest action for MVP. There is no separate follow feature.

Support remains quiet and does not notify the owner.

## Application Lifecycle

### Statuses

Use these internal statuses with friendly labels:

- `pending` -> Pending
- `withdrawn` -> Withdrawn
- `approved` -> Collaborating
- `declined` -> Declined
- `left` -> Left collaboration
- `removed` -> Removed from collaboration

Approved applications remain the collaborator record. Do not introduce a separate collaborator membership table in MVP.

Left and removed applications remain historical applications but no longer count as active collaborators. Only active approved collaborators are publicly visible.

Owners cannot apply to their own ideas. Accepted collaborators cannot apply again while active; they see collaborator state instead.

Declined applications are final as records, but the same user can apply again later. Withdrawn applications are final as records, but the same user can apply again later. There is no hard repeat-application cooldown or limit in MVP beyond one active application at a time and existing throttles.

### Application Threads

Application threads are a UX concept backed by application messages.

Rules:

- Visible only to owner and applicant.
- Existing pending applications get empty thread access.
- Historical approved/declined applications do not get empty thread history created.
- Each application attempt has its own messages/history.
- Declined, withdrawn, approved, left, and removed users retain read-only access to their application thread/history.
- Threads support sanitized markdown.
- Text only; no attachments/files.
- Links are allowed through the existing sanitized markdown path.
- User-sent messages require a body.
- System/action messages may have an empty body.
- Messages are not editable or deletable in MVP.

Lifecycle events create system messages:

- Approval.
- Decline.
- Withdrawal.
- Collaborator leaving.
- Collaborator removal.

Collaborator leaving appends a system message to the original approved application history.

### Unread State

Application threads have a simple unread/new message state, not read receipts.

Track per-user last read timestamp per application, likely through a small read-state table or equivalent model keyed by application and user.

Emails for thread messages send immediately with throttling to prevent bursts.

## Email Notifications

Emails are lightly branded using existing Laravel mail styling.

MVP emails:

- Owner receives a new application email.
- Both sides receive email notifications for new application thread messages.
- Applicant receives an accepted email.
- Applicant receives a declined email.
- Owner receives a withdrawal notification when an applicant withdraws.
- Owner receives a leaving notification when a collaborator leaves.
- Removed collaborator receives a removal notification.
- Accepted collaborators can optionally receive notification when shared getting-started notes are updated, if the owner chooses to notify them.

Email content:

- New application email includes contribution type, first action, and review link, not the full application message.
- Thread message email includes a short preview and link.
- Acceptance email includes a short summary/link only, not full private notes.
- Decline email includes optional private reason if provided.
- Withdrawal/leaving owner notifications include optional reason if provided.
- Removal emails to collaborators include optional private reason if provided.
- Leaving/removal owner emails mention repository access cleanup only when the idea has a connected repository.

No email is sent when a user supports an idea.

## Data And Migration Notes

Propose field names in implementation planning, but allow adjustment to fit the existing codebase.

Potential idea-level fields:

- `collaboration_stage`
- `help_wanted`
- `help_wanted_note`
- `first_contribution`
- `applications_open`
- `applications_closed_note`
- `communication_style`
- `communication_note`
- `getting_started_notes`
- `getting_started_notes_updated_at`

Potential application-level fields:

- `contribution_type`
- `first_action`
- `approval_note`
- `decline_reason`
- `withdrawn_at`
- `left_at`
- `removed_at`

Potential application message fields:

- `idea_application_id`
- `user_id`
- `type`
- `body`
- `occurred_at`

Potential application read-state fields:

- `idea_application_id`
- `user_id`
- `last_read_at`

Rough validation guidance:

- Keep short labels/notes brief and scannable.
- Help wanted note and application first action should be short plain text.
- First contribution and approval note allow links plus line breaks, not rich markdown.
- Full application message, application thread messages, and shared private getting-started notes can use sanitized markdown.

All validation should use Request classes. Data should flow through DTO-backed service and repository boundaries. All timestamps should be stored and retrieved in UTC.

### Existing Ideas

Existing ideas get friendly defaults:

- Stay published.
- Applications open by default.
- Missing collaboration details show "Not decided yet."
- Missing first contribution shows an honest empty state.
- Missing getting-started notes show an honest empty state.

Existing free-text communication values are not migrated into the new structured fields, are not preserved as legacy hints, and are not mentioned to users after rollout. Existing public communication style should show "Not decided yet" until the owner updates it.

No special migration prompt is added; missing readiness checklist items are enough.

### Existing Applications

Existing applications are not migrated into contribution type or first action. They remain reviewable with:

- Existing message/body.
- "Not specified" for new contribution type and first action fields.

Existing pending applications get empty application thread access. Historical approved/declined applications do not need empty read-only threads created.

## Security And Privacy

- Do not expose private contact details by default.
- Public communication style describes preference only.
- Direct contact details belong in private getting-started notes after approval.
- Private getting-started notes are visible only to active collaborators and the owner.
- Application threads are visible only to the owner and applicant.
- Application visibility remains limited by authorization.
- Markdown rendering must reuse the existing sanitized rendering path.
- Application messages, comments, and forms must keep existing validation and throttling protections where applicable.
- Repository invite and cleanup prompts must remain permission-checked.
- Low friction does not override clear privacy/security risks.

## Acceptance Criteria

- A new idea can be created through the two-step flow and published from collaboration setup.
- Drafts persist across both creation steps.
- Step 2 can be completed with undecided collaboration details.
- Editing an existing idea exposes collaboration sections in one efficient form.
- Existing ideas stay usable after migration with friendly defaults.
- The idea sidebar shows Start Collaborating first.
- Guests can see public collaboration guidance and get sign-in/register CTAs.
- Signed-in users can comment before applying.
- Comments remain publicly readable.
- Idea cards show stage, up to three help areas, closed-applications state when relevant, and a first-step badge when relevant.
- Help wanted uses selected areas plus short plain text.
- Applications open/closed is separate from collaboration stage.
- Closing applications blocks new applications but preserves pending applications, comments, and support.
- Applicants can apply with contribution type, first action, and optional markdown message.
- Applicants can choose any contribution type, with owner-selected areas recommended.
- Applicants can edit one pending active application per idea.
- Applicants can withdraw pending applications through a confirm step.
- Users can reapply after declined or withdrawn applications.
- Owners cannot apply to their own ideas.
- Active collaborators cannot apply again.
- Owners receive new application emails.
- Application threads are private to owner and applicant.
- Thread messages notify both sides by email with throttling.
- Application threads become read-only after final states.
- Owner review surfaces show contribution type, first action, optional message, and thread state.
- Owners can approve, decline with optional reason, or ask questions in the thread.
- Decline uses a confirm step and notifies the applicant.
- Approval makes the applicant an active collaborator using the approved application record.
- Approval prompts repository invitation when relevant but does not block on it.
- Accepted collaborators can see shared private getting-started notes and optional approval note.
- Accepted collaborators get an acceptance email with summary/link only.
- Collaborators can leave with optional private reason to owner.
- Owners can remove collaborators with optional private reason and collaborator notification.
- Left/removed collaborators lose access to private getting-started notes and are no longer public collaborators.
- Owner is prompted to review repository access after leave/removal when a connected repository exists.
- Support remains available and quiet when applications are closed.
- Owner readiness checklist appears during creation step 2 and on manage pages.
- Public readiness badges include applications open, first step listed, repo available, and start notes ready.
- The implementation does not introduce a standalone project-management or hiring workflow.

## Delivery Plan

### PR Workflow

Use an epic PR workflow:

- Create an epic branch from `dev`, for example `feat/start-collaborating`.
- Create slice branches from the epic branch.
- Open slice PRs into the epic branch.
- Merge slice PRs into epic after checks pass.
- The epic branch must remain fully shippable after every slice merge.
- Every slice PR must include relevant tests before merging into epic.
- UI-changing slice PRs get smoke browser verification.
- The final epic PR gets full flow browser verification across desktop and mobile.
- The final epic PR from `feat/start-collaborating` into `dev` is the user review/approval surface.

### Suggested Slices

#### Slice 1: Data Foundation And Existing-State Defaults

- Add collaboration setup fields.
- Add application contribution fields.
- Add statuses for withdrawn, left, and removed.
- Add application messages and read state.
- Handle existing ideas and existing applications with friendly defaults.
- Keep the epic branch shippable with old UI still functional.

#### Slice 2: Two-Step Idea Creation And Edit Sections

- Split creation into idea basics and collaboration setup.
- Move repository name to collaboration setup.
- Add draft persistence across both steps.
- Add owner readiness checklist to creation step 2.
- Keep edit as one form with sections.

#### Slice 3: Start Collaborating Sidebar And Idea Cards

- Add Start Collaborating as the first sidebar section.
- Add public guidance, primary actions, and readiness badges.
- Update idea cards with stage/help/closed/badge surfaces.
- Keep existing collaborator/supporter/repository sidebar content secondary.

#### Slice 4: Application Intent And Pending State

- Add contribution type and first action to application flow.
- Recommend owner-selected help areas without blocking other choices.
- Add helpful pending state.
- Allow pending application editing and withdrawal.
- Add owner new-application email.

#### Slice 5: Application Threads

- Add private owner/applicant message thread over application messages.
- Add unread state.
- Add email notifications with throttling.
- Add read-only final states.
- Add lifecycle system messages.

#### Slice 6: Owner Review Decisions

- Refine manage page review cards.
- Add approval behavior, decline confirm with optional reason, and missing-start-notes approval warning/optional approval note.
- Add accepted/declined emails.
- Prompt repository invite after approval when relevant.

#### Slice 7: Accepted Collaborator Start State And Exits

- Add private shared getting-started notes and updated timestamp.
- Add optional notification on notes update.
- Add collaborator leave flow.
- Add owner remove collaborator flow.
- Add repository access cleanup prompts/reminders.
- Ensure left/removed visibility and access rules are enforced.

#### Slice 8: Final Epic Hardening

- Full desktop/mobile browser verification.
- Cross-state authorization checks.
- Email rendering checks.
- Empty-state review.
- Performance/security pass.

## Verification Plan

- Run backend feature tests through the PHP Docker container.
- Run frontend tests and typecheck through the Node Docker container.
- Add/update backend tests for:
  - idea creation/edit validation
  - existing idea defaults
  - application submission/edit/withdraw/reapply
  - application status transitions
  - application thread authorization
  - read-state behavior
  - approval/decline/leave/remove lifecycle
  - private getting-started notes visibility
  - email notifications
  - repository prompt conditions
- Add/update frontend tests for:
  - two-step creation
  - readiness checklist
  - Start Collaborating panel viewer states
  - idea card surfaces
  - application form and pending state
  - owner review cards
  - application thread rendering
  - accepted collaborator start state
  - leave/remove confirmations
- Browser-check changed UI surfaces per slice.
- Final epic browser verification must cover:
  - owner creates idea
  - applicant applies
  - applicant sees pending state
  - owner/applicant exchange thread messages
  - owner approves
  - collaborator sees getting-started state
  - owner declines a separate application
  - applicant withdraws another pending application
  - collaborator leaves
  - owner removes another collaborator
  - applications closed state with comments/support still open
  - guest visibility
  - mobile layout

## Risks

- Adding too many fields could make idea creation feel heavier.
- Two-step creation could feel like onboarding friction if the second step is too dense.
- Application threads could drift into a general messaging system if not tightly scoped.
- Email notifications could become noisy without throttling.
- Public comments without reporting could create moderation needs later.
- Discarding old communication values is a privacy/simplicity tradeoff and may surprise owners with older ideas.
- A partial rollout could improve creation but still leave the approval-to-start gap unresolved.
- Existing idea cards and sidebars may become visually crowded if every collaboration signal is surfaced at once.
- Owner disappearance remains unresolved in MVP; pending applications stay pending.

## Open Questions

None. The PRD is ready for implementation planning.

## Links

- Current idea form: `resources/js/pages/Ideas/Form.vue`
- Current application form: `resources/js/pages/Ideas/Apply.vue`
- Current idea page: `resources/js/pages/Ideas/Show.vue`
- Current idea sidebar: `resources/js/components/ideas/IdeaSidebar.vue`
- Current owner manage page: `resources/js/pages/Ideas/Manage.vue`
