<?php

use App\Enums\CommunityPostType;
use App\Enums\Visibility;
use App\Models\CommunityPost;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public string $postType = '';

    public string $body = '';

    public string $tagsText = '';

    public function mount(): void
    {
        $this->postType = CommunityPostType::Discussion->value;
    }

    public function submit(): void
    {
        $this->authorize('create', CommunityPost::class);

        $this->validate([
            'postType' => ['required', Rule::enum(CommunityPostType::class)],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
            'tagsText' => ['nullable', 'string', 'max:200'],
        ]);

        $tags = collect(explode(',', $this->tagsText))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->values();

        auth()->user()->communityPosts()->create([
            'post_type' => $this->postType,
            'body' => $this->body,
            'tags' => $tags->isEmpty() ? null : $tags->all(),
            'visibility' => Visibility::Community,
            'status' => \App\Enums\CommentStatus::Published->value,
            'likes_count' => 0,
            'comments_count' => 0,
            'is_pinned' => false,
            'is_solved' => false,
        ]);

        $this->redirectRoute('community.index', navigate: false);
    }

    public function render()
    {
        return $this->view([
            'types' => CommunityPostType::cases(),
        ])->layout('layouts::public')->title(__('New post'));
    }
};
?>

<div class="mx-auto w-full max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item :href="route('community.index')" wire:navigate>{{ __('Community') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ __('New post') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <flux:heading size="2xl">{{ __('New post') }}</flux:heading>
    <flux:text class="mt-2 block">
        {{ __('Share what you are building, ask for help, or show off something you finished.') }}
    </flux:text>

    <form wire:submit="submit" class="mt-8 space-y-6 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <flux:radio.group wire:model="postType" label="{{ __('Post type') }}">
            @foreach ($types as $type)
                <flux:radio
                    value="{{ $type->value }}"
                    :label="$type->label()"
                    :description="$type->description()"
                    :checked="$postType === $type->value"
                />
            @endforeach
        </flux:radio.group>

        <flux:textarea
            wire:model="body"
            rows="6"
            label="{{ __('Body') }}"
            placeholder="{{ __('Describe your post. For problems, include what you tried so far.') }}"
        />

        <flux:input
            wire:model="tagsText"
            label="{{ __('Tags') }}"
            hint="{{ __('Comma separated, e.g. laravel, final-year, placement') }}"
            placeholder="laravel, career, placement"
        />

        <div class="flex justify-end gap-3">
            <flux:button tag="a" :href="route('community.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ __('Publish post') }}
            </flux:button>
        </div>
    </form>
</div>