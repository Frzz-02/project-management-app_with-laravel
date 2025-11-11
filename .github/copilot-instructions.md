# Project Management App - AI Coding Instructions

## Project Overview
Laravel 12 project management application with Kanban boards, built using Laravel, Tailwind CSS, Alpine.js, and Vite. Uses SQLite database with eloquent relationships modeling a hierarchical project structure: Projects → Boards → Cards → Subtasks/Comments.

## Architecture & Key Patterns

### Domain Model Hierarchy
```
User (creator/members) → Project → ProjectMember (roles: team lead, member)
                      → Board (kanban boards with position ordering)
                        → Card (tasks with status, priority, time tracking)
                          → Subtask, Comment, CardAssignment, TimeLog
```

**Critical relationships:**
- Projects have `created_by` (creator) + many ProjectMembers with roles
- Boards are position-ordered within projects (`position` field)
- Cards use enum status: `todo|in progress|review|done` and priority: `low|medium|high`
- All models use extensive Indonesian comments explaining business logic

### Controller Patterns
Controllers follow Laravel resource pattern with extensive authorization checks:
- **Always use DB transactions** for multi-model operations (see `ProjectController::store()`)
- **Authorization pattern**: Check `created_by` OR role-based access via ProjectMember
- **Eager loading pattern**: Use `with()` to prevent N+1 queries (see index methods)
- **Statistics calculation**: Controllers compute derived data (deadlines, counts) before passing to views

### Component Architecture
**Blade Components**: Nested X-components with extensive prop passing
- `<x-ui.board-container>` wraps `<x-ui.board-card>` which contains `<x-ui.task-board-list>`
- Components use Alpine.js for state management and event dispatching
- Modal pattern: `@click="$dispatch('add-board-modal')"` triggers modal components
- Components pass complex objects as props: `:project="$project"`, `:isCardStatus="true"`

### Alpine.js Integration
- Global Alpine state in `layouts/app.blade.php` for sidebar, notifications
- Component-level state for edit modes and UI toggles
- Event system for modal triggers and cross-component communication
- Extensive use of transitions and conditional rendering (`x-show`, `x-transition`)

## Development Workflows

### Database Operations
```bash
# Key commands for this Laravel 12 + SQLite setup
php artisan migrate          # Run migrations
php artisan db:seed          # Seed with factories (all models have factories)
php artisan tinker           # REPL with models loaded
```

### Build & Development
```bash
# From composer.json "dev" script - runs all services concurrently
composer dev   # Starts: artisan serve, queue:listen, pail (logs), npm run dev

# Individual services
php artisan serve            # Laravel server
npm run dev                  # Vite development server  
php artisan queue:listen     # Queue worker
php artisan pail --timeout=0 # Real-time logs
```

### Testing
```bash
composer test   # Runs PHPUnit with config clear
```

## Code Conventions

### Model Patterns
- **Extensive documentation**: Every model has detailed PHPDoc blocks in Indonesian
- **Relationship naming**: Use descriptive names (`creator()` not `user()`, `members()` not `projectMembers()`)
- **Accessors for UI logic**: `getDeadlineStatusAttribute()`, `getDeadlineColorAttribute()` for view-specific calculations
- **Scopes for queries**: `scopeOverdue()`, `scopeDueSoon()` for reusable query logic
- **Helper methods**: `canUserEdit()`, `isCreator()` for authorization logic

### Blade View Patterns
- **Layout inheritance**: `@extends('layouts.app')` with `@yield('content')`
- **Component organization**: UI components in `resources/views/components/ui/`
- **Prop passing**: Extensive use of complex props (objects, computed values)
- **Conditional rendering**: Use `@if(!$isCardStatus)` pattern for mode-based UI
- **Indonesian comments**: Detailed explanations of component functionality

### Frontend Stack
- **Tailwind**: Extensive use of glassmorphism (`backdrop-blur-xl`, `bg-white/60`)
- **Alpine.js**: Component state management, no Vue/React
- **Vite**: Asset compilation with Laravel Vite plugin
- **Icons**: Heroicons SVG sprites embedded inline

## Integration Points

### Authentication Flow
- Custom `AuthenticationController` (not Breeze/Sanctum)
- Session-based auth with `Auth::id()` checks throughout
- No API routes - purely web-based application

### Database Configuration
- SQLite database (`database/database.sqlite`)
- Factories for all models with realistic fake data
- Extensive use of foreign key constraints with cascade deletes

### Component Communication
- Alpine.js event system for modal triggers
- Parent-child prop passing for state sharing  
- No Ajax/API calls - uses traditional form submissions

## Key Files to Reference
- `app/Models/Project.php` - Demonstrates relationship patterns, accessors, scopes
- `app/Http/Controllers/web/ProjectController.php` - Shows authorization, transactions, eager loading
- `resources/views/layouts/app.blade.php` - Alpine.js setup, component structure
- `resources/views/components/ui/board-container.blade.php` - Component patterns, event dispatching
- `routes/web.php` - Resource routes with custom additional routes pattern

When working with this codebase, prioritize maintaining the existing patterns: extensive documentation, role-based authorization, Alpine.js state management, and the hierarchical component architecture.