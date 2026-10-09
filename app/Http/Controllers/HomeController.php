<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\BlogPost;
use App\Models\CommunityPost;
use App\Models\Course;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredCourses = Course::query()
            ->published()
            ->with('skill')
            ->withCount(['modules as module_count' => fn ($query) => $query->where('is_published', true)])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $latestPosts = BlogPost::query()
            ->published()
            ->with(['author', 'primaryCategory'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $communityPosts = CommunityPost::query()
            ->published()
            ->with('author')
            ->ranked()
            ->limit(3)
            ->get();

        return view('marketing.home', [
            'stats' => [
                [
                    'label' => 'Free courses',
                    'value' => Course::query()->published()->count(),
                    'route' => 'courses.index',
                ],
                [
                    'label' => 'Community posts',
                    'value' => CommunityPost::query()->published()->count(),
                    'route' => 'community.index',
                ],
                [
                    'label' => 'Insights',
                    'value' => BlogPost::query()->published()->count(),
                    'route' => 'blog.index',
                ],
                [
                    'label' => 'Interns placed',
                    'value' => User::query()->where('role', UserRole::Student->value)->count(),
                    'route' => null,
                ],
            ],
            'featuredCourses' => $featuredCourses,
            'latestPosts' => $latestPosts,
            'communityPosts' => $communityPosts,
        ]);
    }
}
