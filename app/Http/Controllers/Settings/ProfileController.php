<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user()->load([
            'division:id,name',
            'position:id,name',
            'branch:id,name',
            'manager:id,name',
        ]);

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => Features::enabled(Features::emailVerification()),
            'status' => $request->session()->get('status'),
            'organization' => [
                'employee_id' => $user->employee_id,
                'division' => $user->division?->only(['name']),
                'position' => $user->position?->only(['name']),
                'branch' => $user->branch?->only(['name']),
                'manager' => $user->manager?->only(['name']),
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Profil berhasil diperbarui.']);

        return to_route('profile.edit');
    }
}
