<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportPrintController extends Controller
{
    public function __invoke(Request $request, Report $report)
    {
        $viewer = $request->user();

        $isOwner = $report->studentProfile?->user_id === $viewer->id;
        $isAdmin = in_array($viewer->role, [UserRole::Admin, UserRole::SuperAdmin], true);
        $isSupervisor = $viewer->role === UserRole::Supervisor
            && $report->studentProfile?->placement?->supervisor_id === $viewer->id;

        abort_unless($isOwner || $isAdmin || $isSupervisor, 403);

        return view('print.report', [
            'report' => $report->load(['studentProfile.user', 'template', 'currentVersion.sections']),
        ]);
    }
}
