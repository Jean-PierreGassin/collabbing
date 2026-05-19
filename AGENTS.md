# Project Overview

## Stack
- PHP 8.5
- MySQL 
- Vue 3/TypeScript
- Tailwind
- Vite

## Organisation
- Prioritise organising files/classes/methods to ensure we are always in a clean workspace
- Before planning or making a change, think about where it belongs
- Consider long files/classes/methods and think about breaking them down/splitting them out
  - This must not add extra complexity/bloat
  - This must make sense and not conflict with other requirements in this overview

## Architecture
- Separate concerns and do not mix layers
- Use Request classes to better structure input
- Use Controllers/Services/Repositories to separate layers
- Use DTO's to ensure data is passed around in expected structures
- When making decisions around database architecture, prefer simplicity without sacrificing scalability
- All times should be stored as UTC and retrieved as UTC, then converted to the users timezone for display
  - Consider this heavily when events must occur in the future based on the users timezone
- Do not write rollback code for migrations, only roll forward

## Code Quality
- Use PSR-12 standards
- Ensure changes pass Pint/PHPStan/ESLint configuration
- Files/Methods/Classes should have realistic human friendly names
- Do not use docblocks unless you think it's required to explain something critical
- Use named arguments, return types, typed arguments, and constructor property promotion
- Use conditional statements instead of ternary statements
- Use Carbon over DateTime in all circumstances

## Performance
- When making changes, always review for performance and adjust accordingly
  - This must not add extra complexity/bloat
  - This must make sense and not conflict with other requirements in this overview

## Security
- Use appropriate request validation (e.g using input() to access)
- When making changes, always review for security
  - This must not add extra complexity/bloat
  - This must make sense and not conflict with other requirements in this overview

## Tests
- When writing tests, ensure we test for real scenarios including:
  - Happy paths
  - Unhappy paths
  - Edge cases
- Always consider using data providers when test cases repeat the same behaviour with different inputs
- Unit tests must avoid database access
- Use camel case method names, prefixed with `test`
- Do not create long/verbose test method names
- Tests should be brief and readable/easy to follow, and well designed
- Tests must prioritise critical application business logic over shape checks or framework/library behaviour

## Version Control
- Never work directly off `dev` or `master`, create a branch targeting `dev` instead
- Branches must be prefixed with either `feat/`, `fix/`, `hotfix/`, or `task/` depending on their context
  - e.g `feat/implement-something-great`, `hotfix/fpm-is-on-fire`, `task/this-is-boring`
- Commit messages must be formatted like: `feat/hotfix - Short Title Summary`, followed by a bullet point list of what changed
- Before committing, review your work and ensure quality and standards are met
- Always commit incrementally as you work
