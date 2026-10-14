<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Family;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function security(): View
    {
        $user = auth()->user();
        $family = $user->currentFamily();

        $auditLogs = AuditEvent::with('actor')
            ->where('family_id', $family->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('settings.security', compact('user', 'family', 'auditLogs'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Your password has been changed successfully.');
    }

    public function updateFamily(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $family = $user->currentFamily();

        if (!$user->canManageFamily($family->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $family->update(['name' => $validated['name']]);

        AuditEvent::log(
            $family->id,
            'family.renamed',
            'Family',
            $family->id,
            ['new_name' => $family->name]
        );

        return back()->with('success', 'Family workspace name updated.');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }
}

