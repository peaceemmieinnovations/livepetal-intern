<x-layouts::auth :title="__('Create account')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create your account')" :description="__('Browse free courses, take part in the community and apply for intern access.')" />

        <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Full name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Jane Doe"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
            />

            <!-- Application Note -->
            <flux:field>
                <flux:label>{{ __('Why do you want to intern with Livepetal?') }} <flux:badge size="sm" inset="top bottom">{{ __('optional') }}</flux:badge></flux:label>
                <flux:textarea
                    name="application_note"
                    rows="3"
                    maxlength="600"
                    :placeholder="__('A short note helps administrators review your application.')"
                >{{ old('application_note') }}</flux:textarea>
                <flux:error name="application_note" />
            </flux:field>

            <flux:button variant="primary" type="submit" class="w-full" data-test="register-button">
                {{ __('Create account') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>