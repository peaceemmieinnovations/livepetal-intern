<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportPrintController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::livewire('courses', 'pages::courses-index')->name('courses.index');
Route::livewire('courses/{course}', 'pages::courses-show')->name('courses.show');

Route::livewire('community', 'pages::community-index')->name('community.index');
Route::livewire('community/create', 'pages::community-create')->middleware('auth')->name('community.create');
Route::livewire('community/{post}', 'pages::community-show')->name('community.show');

Route::livewire('blog', 'pages::blog-index')->name('blog.index');
Route::livewire('blog/{post}', 'pages::blog-show')->name('blog.show');

Route::livewire('students/{username}', 'pages::public-portfolio')->name('students.show');

Route::view('products', 'marketing.products')->name('products.index');
Route::view('services', 'marketing.services')->name('services.index');
Route::view('hire', 'marketing.hire')->name('hire');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (Request $request) {
        return redirect()->route($request->user()->dashboardRouteName());
    })->name('dashboard');

    Route::livewire('member', 'pages::member-dashboard')
        ->middleware('role:member')
        ->name('member.dashboard');

    Route::get('reports/{report}/print', ReportPrintController::class)->name('reports.print');

    Route::livewire('student', 'pages::student-dashboard')
        ->middleware(['role:student', 'intern'])
        ->name('student.dashboard');

    Route::livewire('student/activities', 'pages::student-activities')
        ->middleware(['role:student', 'intern'])
        ->name('student.activities.index');

    Route::livewire('student/projects', 'pages::student-projects')
        ->middleware(['role:student', 'intern'])
        ->name('student.projects.index');

    Route::livewire('student/skills', 'pages::student-skills')
        ->middleware(['role:student', 'intern'])
        ->name('student.skills');

    Route::livewire('student/portfolio', 'pages::student-portfolio')
        ->middleware(['role:student', 'intern'])
        ->name('student.portfolio');

    Route::livewire('student/weekly-summaries', 'pages::student-weekly')
        ->middleware(['role:student', 'intern'])
        ->name('student.weekly');

    Route::livewire('student/reports', 'pages::student-reports')
        ->middleware(['role:student', 'intern'])
        ->name('student.reports.index');

    Route::livewire('supervisor', 'pages::supervisor-dashboard')
        ->middleware(['role:supervisor', 'intern'])
        ->name('supervisor.dashboard');

    Route::livewire('supervisor/students', 'pages::supervisor-students')
        ->middleware(['role:supervisor', 'intern'])
        ->name('supervisor.students.index');

    Route::livewire('admin', 'pages::admin-dashboard')
        ->middleware('role:admin,super_admin')
        ->name('admin.dashboard');

    Route::livewire('admin/approvals', 'pages::admin-approvals')
        ->middleware('role:admin,super_admin')
        ->name('admin.approvals');
});

require __DIR__.'/settings.php';
