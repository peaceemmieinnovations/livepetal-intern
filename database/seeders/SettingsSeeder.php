<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Platform settings, including the benefits an administrator promises to
 * approved interns. The benefits list is configuration, not code: the admin
 * console edits it, and the public site renders whatever is there.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::flushCache();

        Setting::putMany([
            'site.name' => 'Livepetal Intern',
            'site.tagline' => 'SIWES internships, reports and community, in one place.',
            'site.contact_email' => 'hello@livepetalintern.test',
            'site.support_email' => 'support@livepetalintern.test',
            'site.instagram_url' => 'https://instagram.com/livepetal',
            'site.linkedin_url' => 'https://linkedin.com/company/livepetal',
            'site.x_url' => 'https://x.com/livepetal',
        ], 'general');

        // Whether public registration is switched on at all. Turning it off hides
        // the sign-up route without deleting anyone's account.
        Setting::put('registrations.open', '1', 'registrations', 'boolean');
        Setting::put(
            'registrations.welcome',
            'Create an account to browse free courses, join the community and post your problems. '
            .'Intern access to reports, placements and supervisors is granted by an administrator after approval.',
            'registrations',
            'text',
        );

        // The internship application switch. One switch per opportunity lives on
        // the job posting itself; this is the copy shown next to it.
        Setting::put(
            'applications.instructions',
            'Applications open for a limited window. The deadline shown on each listing is final.',
            'applications',
            'text',
        );

        Setting::putMany([
            'benefits.0.title' => 'SIWES placement at Livepetal Systems Limited',
            'benefits.0.body' => 'A supervised, evidence-backed placement with a real engineering team.',
            'benefits.1.title' => 'Personal data bundle while you are on placement',
            'benefits.1.body' => 'Data allowance granted for approved interns, funded for the duration of the internship.',
            'benefits.2.title' => 'Certificates for every course you finish',
            'benefits.2.body' => 'Verifiable certificates for the free courses, plus a verified work reference on completion.',
            'benefits.3.title' => 'Approved work history in your public portfolio',
            'benefits.3.body' => 'Projects and skills verified by your supervisor are visible to recruiters.',
            'benefits.4.title' => 'Priority access to paid internships and opportunities',
            'benefits.4.body' => 'Approved interns see internal roles before they are published anywhere else.',
        ], 'benefits');

        Setting::put('reports.auto_publish', '0', 'reports', 'boolean');
        Setting::put('community.require_approval_for_new_posters', '0', 'community', 'boolean');
    }
}
