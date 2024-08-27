<?php

namespace App\Domains\D\Http\Controllers\Auth;

use App\Domains\D\Http\Controllers\Controller;
use App\Domains\D\Http\Resources\AdminResource;
use App\Domains\D\Notifications\VerifyNewEmail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return new AdminResource($request->user());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
        ]);

        $request->user()->update($request->only('first_name', 'last_name', 'phone', 'dob'));

        return new AdminResource($request->user());
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password', 'string'],
            'password' => ['required',  'string', Rules\Password::defaults()],
        ], [
            'current_password.current_password' => 'The provided password does not match your current password.'
        ]);

        $request->user()->update([
            'password' => Hash::make($request->string('password')),
        ]);

        event(new PasswordReset($request->user()));

        return response()->noContent();
    }

    /**
     * Send email change verification notification.
     */
    public function sendEmailChangeVerificationNotification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', Rule::unique('admins')],
        ]);

        Notification::route('mail', $request->string('email'))
            ->notify(new VerifyNewEmail($request->user()));

        return response()->noContent();
    }

    /**
     * Verify the new email address
     */
    public function verifyNewEmail(Request $request)
    {
        if (! hash_equals((string) $request->user()->getKey(), (string) $request->route('id'))) {
            abort(403);
        }

        if (! hash_equals(sha1($request->route('email')), (string) $request->route('hash'))) {
            abort(403);
        }

        $request->user()->update([
            'email' => $request->route('email'),
            'email_verified_at' => now(),
        ]);

        event(new Verified($request->user()));

        return response()->noContent();
    }
}
