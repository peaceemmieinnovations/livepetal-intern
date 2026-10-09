<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the platform accounts used for local development and demos. Passwords
 * are deliberately obvious; this seeder must never run in production.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $accounts = [
            [
                'name' => 'Livepetal Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@livepetalintern.test',
                'role' => UserRole::SuperAdmin,
            ],
            [
                'name' => 'Livepetal Admin',
                'username' => 'admin',
                'email' => 'admin@livepetalintern.test',
                'role' => UserRole::Admin,
            ],
            [
                'name' => 'Ada Supervisor',
                'username' => 'supervisor',
                'email' => 'supervisor@livepetalintern.test',
                'role' => UserRole::Supervisor,
            ],
            [
                'name' => 'Tomi Supervisor',
                'username' => 'supervisor2',
                'email' => 'supervisor2@livepetalintern.test',
                'role' => UserRole::Supervisor,
            ],
            [
                'name' => 'Chidi Student',
                'username' => 'student',
                'email' => 'student@livepetalintern.test',
                'role' => UserRole::Student,
            ],
            [
                'name' => 'Ngozi Student',
                'username' => 'student2',
                'email' => 'student2@livepetalintern.test',
                'role' => UserRole::Student,
            ],
            [
                'name' => 'Sample Member',
                'username' => 'member',
                'email' => 'member@livepetalintern.test',
                'role' => UserRole::Member,
                'approved_at' => null,
                'applied_at' => now(),
            ],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'username' => $account['username'],
                    'role' => $account['role']->value,
                    'status' => User::STATUS_ACTIVE,
                    'email_verified_at' => now(),
                    'password' => $password,
                    'onboarding_completed_at' => now(),
                    'approved_at' => $account['approved_at'] ?? now(),
                    'applied_at' => $account['applied_at'] ?? null,
                ],
            );
        }
    }
}
