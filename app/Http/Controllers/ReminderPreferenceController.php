<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\ReminderPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderPreferenceController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $family = $user->currentFamily();

        $familyPref = $family->defaultReminderPreference();

        $userPref = ReminderPreference::where('family_id', $family->id)
            ->where('user_id', $user->id)
            ->first();

        $isManager = $user->canManageFamily($family->id);

        $availableTimezones = [
            'Asia/Kolkata' => '(GMT+05:30) India Standard Time - Kolkata, Mumbai, New Delhi',
            'Asia/Dubai' => '(GMT+04:00) Gulf Standard Time - Dubai',
            'Asia/Singapore' => '(GMT+08:00) Singapore Standard Time',
            'Europe/London' => '(GMT+00:00) London / GMT',
            'America/New_York' => '(GMT-05:00) Eastern Time - New York',
            'America/Los_Angeles' => '(GMT-08:00) Pacific Time - Los Angeles',
            'UTC' => 'UTC (Coordinated Universal Time)',
        ];

        return view('settings.reminders', compact(
            'family',
            'familyPref',
            'userPref',
            'isManager',
            'availableTimezones'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $family = $user->currentFamily();
        $isManager = $user->canManageFamily($family->id);

        $validated = $request->validate([
            'target' => ['required', 'string', 'in:family,user'],
            'enabled' => ['nullable', 'boolean'],
            'offsets' => ['required', 'array'],
            'offsets.*' => ['integer', 'min:0', 'max:365'],
            'send_time' => ['required', 'string'],
            'timezone' => ['required', 'string'],
        ]);

        $offsets = array_values(array_unique(array_map('intval', $validated['offsets'])));
        rsort($offsets); // Sort descending (e.g. 30, 7, 1)

        $enabled = $request->boolean('enabled');

        if ($validated['target'] === 'family' && $isManager) {
            $pref = $family->defaultReminderPreference();
            $pref->update([
                'default_offsets_json' => json_encode($offsets),
                'enabled' => $enabled,
                'send_time' => $validated['send_time'],
                'timezone' => $validated['timezone'],
            ]);

            $family->update(['timezone' => $validated['timezone']]);

            AuditEvent::log(
                $family->id,
                'settings.reminders_updated',
                'ReminderPreference',
                $pref->id,
                ['level' => 'family', 'enabled' => $enabled, 'offsets' => $offsets]
            );

            return back()->with('success', 'Family workspace reminder defaults have been saved.');
        } else {
            // User personal preference
            $pref = ReminderPreference::updateOrCreate(
                [
                    'family_id' => $family->id,
                    'user_id' => $user->id,
                ],
                [
                    'default_offsets_json' => json_encode($offsets),
                    'enabled' => $enabled,
                    'send_time' => $validated['send_time'],
                    'timezone' => $validated['timezone'],
                ]
            );

            $user->update(['timezone' => $validated['timezone']]);

            AuditEvent::log(
                $family->id,
                'settings.personal_reminders_updated',
                'ReminderPreference',
                $pref->id,
                ['level' => 'personal', 'enabled' => $enabled]
            );

            return back()->with('success', 'Your personal reminder preferences have been saved.');
        }
    }
}

