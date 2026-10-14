<?php

namespace App\Http\Middleware;

use App\Models\AuditEvent;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\ReminderPreference;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveFamily
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $familyId = session('active_family_id');

            $family = null;
            if ($familyId) {
                $family = $user->families()->where('families.id', $familyId)->first();
            }

            if (!$family) {
                $family = $user->families()->first();
            }

            // If user has no family yet, create their default household
            if (!$family) {
                $family = Family::create([
                    'name' => $user->name . "'s Household",
                    'owner_user_id' => $user->id,
                    'timezone' => $user->timezone ?? 'Asia/Kolkata',
                ]);

                FamilyMember::create([
                    'family_id' => $family->id,
                    'user_id' => $user->id,
                    'role' => 'owner',
                    'joined_at' => now(),
                ]);

                ReminderPreference::create([
                    'family_id' => $family->id,
                    'user_id' => null,
                    'default_offsets_json' => json_encode([30, 7, 1]),
                    'enabled' => true,
                    'send_time' => '09:00',
                    'timezone' => $family->timezone,
                ]);

                AuditEvent::log(
                    $family->id,
                    'family.created',
                    'Family',
                    $family->id,
                    ['name' => $family->name]
                );
            }

            session(['active_family_id' => $family->id]);

            // Share current family and user's role with all views
            View::share('currentFamily', $family);
            View::share('currentUserRole', $user->roleInFamily($family->id));
            View::share('userFamilies', $user->families()->get());
        }

        return $next($request);
    }
}

