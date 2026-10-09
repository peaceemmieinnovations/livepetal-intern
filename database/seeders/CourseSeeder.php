<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The free course catalogue. Every course is built from real curriculum
 * structure rather than placeholder modules.
 */
class CourseSeeder extends Seeder
{
    /**
     * @var array<int, array{title: string, summary: string, level: string, skill: string, modules: array<int, array{title: string, body: string, minutes: int}>}>
     */
    private const COURSES = [
        [
            'title' => 'Version control with Git for beginners',
            'summary' => 'Branch, commit, review and undo confidently so you stop losing work.',
            'level' => 'beginner',
            'skill' => 'Git',
            'modules' => [
                ['title' => 'Why version control exists', 'body' => "Every serious team shares one history of a codebase. This lesson covers what a repository actually stores, why email attachments of \"final_v7\" files fail, and how a single shared history removes guesswork.\n\nOutcome: you can explain a repository, a commit and a working tree to someone else.", 'minutes' => 15],
                ['title' => 'Committing your first changes', 'body' => "Run git status, add a file, commit with a message that reads well six months later, and inspect what happened with git log.\n\nOutcome: a clean first commit and a readable history.", 'minutes' => 25],
                ['title' => 'Branching without fear', 'body' => "Create a branch for a fix, keep main stable, and merge it back. Covers why teams branch per change rather than per developer for weeks.\n\nOutcome: you can isolate a change without touching the main line.", 'minutes' => 30],
                ['title' => 'Fixing mistakes safely', 'body' => "Undo a staged file, amend a local commit, revert a bad commit that others already have, and recover a deleted branch.\n\nOutcome: you know which undo is safe to use when.", 'minutes' => 35],
                ['title' => 'Working with a remote team', 'body' => "Pull, push, resolve the classic \"your local history has diverged\" error, and resolve a merge conflict by hand.\n\nOutcome: you can contribute to a shared repository with other people.", 'minutes' => 40],
            ],
        ],
        [
            'title' => 'Laravel fundamentals: from route to database',
            'summary' => 'Build a working CRUD feature and understand what each layer is responsible for.',
            'level' => 'beginner',
            'skill' => 'Laravel',
            'modules' => [
                ['title' => 'Routing and controllers', 'body' => "A request enters the app, matches a route, and lands in a controller action. This lesson walks a GET and a POST through that path.\n\nOutcome: you can read a route definition and predict where it lands.", 'minutes' => 20],
                ['title' => 'Eloquent models and migrations', 'body' => "Declare the table shape, generate the model, and let Eloquent do the SQL. Covers relationships you will read most: hasOne, hasMany, belongsTo.\n\nOutcome: you can model a resource with relationships.", 'minutes' => 35],
                ['title' => 'Validation before you write', 'body' => "Bad input is a fact of life. Validate at the boundary and fail with a message the user can act on.\n\nOutcome: your feature rejects bad data without a stack trace.", 'minutes' => 25],
                ['title' => 'Views and the request lifecycle', 'body' => "Pass data to a view, render a Blade template, and understand the full lifecycle from HTTP request to response.\n\nOutcome: you can trace a request end to end.", 'minutes' => 30],
                ['title' => 'Testing the feature you just built', 'body' => "Write a feature test that hits the routes and asserts the database rows. This is what stops the feature from silently breaking.\n\nOutcome: a passing test suite for your CRUD feature.", 'minutes' => 40],
            ],
        ],
        [
            'title' => 'Communication that gets your work approved',
            'summary' => 'Status updates, code review replies and asking for help without losing time.',
            'level' => 'beginner',
            'skill' => 'Technical Writing',
            'modules' => [
                ['title' => 'Write the status update people read', 'body' => "What you did, what you plan, what is blocking you. Three lines, in that order.\n\nOutcome: a status update a supervisor can scan in five seconds.", 'minutes' => 15],
                ['title' => 'Ask for help so you get a real answer', 'body' => "Context, what you tried, what happened, what you expected. Attaching the error text rather than a screenshot of it.\n\nOutcome: fewer round trips before someone can unblock you.", 'minutes' => 20],
                ['title' => 'Receive a code review without taking it personally', 'body' => "Read the comment, ask a question if it is unclear, and change the code. Review comments are about the work, not about you.\n\nOutcome: you can act on feedback quickly.", 'minutes' => 20],
            ],
        ],
    ];

    public function run(): void
    {
        $author = User::query()->where('role', 'admin')->first()
            ?? User::query()->where('role', 'super_admin')->first();

        foreach (self::COURSES as $definition) {
            $skill = Skill::query()->where('slug', Str::slug($definition['skill']))->first();

            $course = Course::query()->updateOrCreate(
                ['slug' => Str::slug($definition['title'])],
                [
                    'title' => $definition['title'],
                    'summary' => $definition['summary'],
                    'description' => $definition['summary'],
                    'level' => $definition['level'],
                    'category_id' => $skill?->id,
                    'estimated_minutes' => array_sum(array_column($definition['modules'], 'minutes')),
                    'status' => 'published',
                    'created_by' => $author?->id,
                    'published_at' => now()->subWeeks(3),
                ],
            );

            foreach ($definition['modules'] as $index => $module) {
                $course->modules()->updateOrCreate(
                    ['title' => $module['title']],
                    [
                        'content' => $module['body'],
                        'content_type' => 'text',
                        'duration_minutes' => $module['minutes'],
                        'sort_order' => $index + 1,
                        'is_published' => true,
                    ],
                );
            }
        }
    }
}
