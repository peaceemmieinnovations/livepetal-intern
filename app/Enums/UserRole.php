<?php

namespace App\Enums;

/**
 * Platform roles. Stored as a string on the users table.
 */
enum UserRole: string
{
    /**
     * A self-registered member. Can browse public content, take free courses and
     * take part in the community, but has no intern access until an administrator
     * approves them.
     */
    case Member = 'member';
    case Student = 'student';
    case Supervisor = 'supervisor';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    /**
     * Roles that may reach the administration console.
     *
     * @return array<int, string>
     */
    public static function administrative(): array
    {
        return [self::Admin->value, self::SuperAdmin->value];
    }

    /**
     * Roles an approved member is promoted into when they start a placement.
     *
     * @return array<int, string>
     */
    public static function internRoles(): array
    {
        return [self::Student->value, self::Supervisor->value];
    }

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Member',
            self::Student => 'Intern (Student)',
            self::Supervisor => 'Supervisor',
            self::Admin => 'Administrator',
            self::SuperAdmin => 'Super Administrator',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Member => 'Signed up and browsing. Public courses, community and problem posts are unlocked; intern access waits for approval.',
            self::Student => 'Approved intern documenting a SIWES training journey at Livepetal Systems Limited.',
            self::Supervisor => 'Reviews and supports an assigned group of trainees.',
            self::Admin => 'Manages cohorts, trainees, content and platform operations.',
            self::SuperAdmin => 'Full platform control, including administrator permissions.',
        };
    }

    public function isAdministrative(): bool
    {
        return in_array($this->value, self::administrative(), true);
    }

    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    /**
     * Whether this role may open private trainee records: reports, placements,
     * documents and activity logs.
     */
    public function hasInternAccess(): bool
    {
        return in_array($this->value, self::internRoles(), true);
    }

    public function requiresApproval(): bool
    {
        return $this === self::Member;
    }

    /**
     * Route name prefix for the dashboard a member of this role lands on.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Member => 'member.dashboard',
            self::Student => 'student.dashboard',
            self::Supervisor => 'supervisor.dashboard',
            self::Admin, self::SuperAdmin => 'admin.dashboard',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
    }
}
