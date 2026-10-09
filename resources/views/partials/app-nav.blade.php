@php
    /**
     * Role-aware application navigation.
     *
     * Groups and items are declared as data so each phase of the build can append
     * to one place. Items whose route does not exist yet are skipped, which keeps
     * the sidebar honest while sections are still being built.
     *
     * @var \App\Models\User $user
     */
    $user = auth()->user();
    $role = $user->role;

    $groups = [
        [
            'heading' => __('Platform'),
            'items' => [
                ['label' => __('Dashboard'), 'icon' => 'home', 'route' => $user->dashboardRouteName()],
            ],
        ],
        [
            'heading' => match ($role->value) {
                'member' => __('Getting started'),
                'student' => __('Trainee workspace'),
                'supervisor' => __('Supervision'),
                default => __('Administration'),
            },
            'items' => [
                ['label' => __('Courses'), 'icon' => 'academic-cap', 'route' => 'courses.index'],
                ['label' => __('Community'), 'icon' => 'chat-bubble-left-right', 'route' => 'community.index'],
                ['label' => __('Insights'), 'icon' => 'newspaper', 'route' => 'blog.index'],
                ['label' => __('Activity log'), 'icon' => 'clipboard-document-list', 'route' => 'student.activities.index', 'roles' => ['student']],
                ['label' => __('Projects'), 'icon' => 'code-bracket', 'route' => 'student.projects.index', 'roles' => ['student']],
                ['label' => __('Skills'), 'icon' => 'sparkles', 'route' => 'student.skills', 'roles' => ['student']],
                ['label' => __('Reports'), 'icon' => 'document-text', 'route' => 'student.reports.index', 'roles' => ['student']],
                ['label' => __('Portfolio'), 'icon' => 'folder-git-2', 'route' => 'student.portfolio', 'roles' => ['student']],
                ['label' => __('Weekly summaries'), 'icon' => 'calendar-days', 'route' => 'student.weekly', 'roles' => ['student']],
                ['label' => __('My supervisees'), 'icon' => 'users', 'route' => 'supervisor.students.index', 'roles' => ['supervisor']],
                ['label' => __('Approvals'), 'icon' => 'clipboard-document-check', 'route' => 'admin.approvals', 'roles' => ['admin', 'super_admin']],
                ['label' => __('Content'), 'icon' => 'newspaper', 'route' => 'admin.content.index', 'roles' => ['admin', 'super_admin']],
            ],
        ],
    ];
@endphp

@foreach ($groups as $group)
    @php
        $items = collect($group['items'])
            ->filter(fn (array $item): bool => Route::has($item['route']))
            ->filter(fn (array $item): bool => ! isset($item['roles']) || in_array($role->value, $item['roles'], true))
            ->values();
    @endphp

    @if ($items->isNotEmpty())
        <flux:sidebar.group :heading="$group['heading']" class="grid">
            @foreach ($items as $item)
                <flux:sidebar.item
                    :icon="$item['icon']"
                    :href="route($item['route'])"
                    :current="request()->routeIs($item['route'])"
                    wire:navigate
                >
                    {{ $item['label'] }}
                </flux:sidebar.item>
            @endforeach
        </flux:sidebar.group>
    @endif
@endforeach
