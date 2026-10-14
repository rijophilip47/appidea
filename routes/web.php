<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentAttachmentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentExportController;
use App\Http\Controllers\FamilyInvitationController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\ReminderPreferenceController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('pages.landing');
})->name('home');

Route::get('/privacy', [SettingsController::class, 'privacy'])->name('privacy');
Route::get('/terms', [SettingsController::class, 'terms'])->name('terms');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Invitation Public View (Guest can see who invited them)
Route::get('/invitations/{token}', [FamilyInvitationController::class, 'show'])->name('invitations.show');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Documents CRUD
    Route::resource('documents', DocumentController::class);
    Route::post('/documents/{document}/renew', [DocumentController::class, 'renew'])->name('documents.renew');
    Route::post('/documents/{document}/archive', [DocumentController::class, 'archive'])->name('documents.archive');

    // Document Attachments (Authorized & Private)
    Route::get('/documents/{document}/attachments/{attachment}', [DocumentAttachmentController::class, 'download'])
        ->name('documents.attachments.download');
    Route::delete('/documents/{document}/attachments/{attachment}', [DocumentAttachmentController::class, 'destroy'])
        ->name('documents.attachments.destroy');

    // Family Members & Invitations
    Route::get('/family/members', [FamilyMemberController::class, 'index'])->name('family.members');
    Route::post('/family/members/switch/{family}', [FamilyMemberController::class, 'switchFamily'])->name('family.switch');
    Route::patch('/family/members/{member}/role', [FamilyMemberController::class, 'updateRole'])->name('family.members.updateRole');
    Route::delete('/family/members/{member}', [FamilyMemberController::class, 'destroy'])->name('family.members.destroy');

    Route::post('/family/invitations', [FamilyInvitationController::class, 'store'])->name('family.invitations.store');
    Route::post('/family/invitations/{invitation}/revoke', [FamilyInvitationController::class, 'revoke'])->name('family.invitations.revoke');
    Route::post('/invitations/{token}/accept', [FamilyInvitationController::class, 'accept'])->name('invitations.accept');

    // Reminder Preferences
    Route::get('/settings/reminders', [ReminderPreferenceController::class, 'index'])->name('settings.reminders');
    Route::put('/settings/reminders', [ReminderPreferenceController::class, 'update'])->name('settings.reminders.update');

    // Security & Workspace Settings
    Route::get('/settings/security', [SettingsController::class, 'security'])->name('settings.security');
    Route::put('/settings/security/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::put('/settings/security/family', [SettingsController::class, 'updateFamily'])->name('settings.family.update');

    // CSV Data Export
    Route::post('/exports/documents.csv', [DocumentExportController::class, 'exportCsv'])->name('exports.documents.csv');
});
