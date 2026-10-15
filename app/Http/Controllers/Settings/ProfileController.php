<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

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
            'mustVerifyEmail' => true,
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

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        throw ValidationException::withMessages([
            'profile' => 'Akun internal tidak dapat dihapus untuk menjaga riwayat pembelajaran dan audit. Hubungi LMS Admin untuk menonaktifkan akun.',
        ]);
    }
}
