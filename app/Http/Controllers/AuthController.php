<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\ReminderPreference;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Set default active family
            $user = Auth::user();
            $family = $user->families()->first();
            if ($family) {
                session(['active_family_id' => $family->id]);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register', [
            'invitationToken' => $request->query('invitation'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'family_name' => ['nullable', 'string', 'max:120'],
            'timezone' => ['nullable', 'string'],
        ]);

        $timezone = $validated['timezone'] ?? 'Asia/Kolkata';

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'timezone' => $timezone,
        ]);

        // Create default family workspace (Section 6.1)
        $familyName = !empty($validated['family_name'])
            ? trim($validated['family_name'])
            : $user->name . "'s Household";

        $family = Family::create([
            'name' => $familyName,
            'owner_user_id' => $user->id,
            'timezone' => $timezone,
        ]);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        // Default reminder preferences
        ReminderPreference::create([
            'family_id' => $family->id,
            'user_id' => null,
            'default_offsets_json' => json_encode([30, 7, 1]),
            'enabled' => true,
            'send_time' => '09:00',
            'timezone' => $timezone,
        ]);

        AuditEvent::log(
            $family->id,
            'account.registered',
            'User',
            $user->id,
            ['name' => $user->name, 'email' => $user->email]
        );

        Auth::login($user);
        session(['active_family_id' => $family->id]);

        // If there was an invitation token, redirect to accept it
        if ($request->filled('invitation_token')) {
            return redirect()->route('invitations.show', ['token' => $request->input('invitation_token')]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Your account and family workspace have been created successfully!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'You have been logged out.');
    }
}

