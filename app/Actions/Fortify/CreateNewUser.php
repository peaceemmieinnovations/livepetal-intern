<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered member.
     *
     * A self-registered account is a Member: it may browse public content and
     * join the community, but has no intern access until an administrator
     * approves the account.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => $this->passwordRules(),
            'application_note' => ['nullable', 'string', 'max:600'],
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => Str::lower(trim($input['email'])),
            'password' => $input['password'],
            'application_note' => isset($input['application_note']) ? trim($input['application_note']) : null,
            'role' => UserRole::Member->value,
            'applied_at' => now(),
        ]);
    }
}
