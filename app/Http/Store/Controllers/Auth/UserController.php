<?php

declare(strict_types=1);

namespace App\Http\Store\Controllers\Auth;

use App\Http\Store\Controllers\Controller;
use App\Http\Store\Resources\CustomerResource;
use App\Models\Customer;
use App\Notifications\Store\VerifyNewEmail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): CustomerResource
    {
        return new CustomerResource($request->user());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): CustomerResource
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
        ]);

        /** @var array<string,mixed> $attributes */
        $attributes = $request->only('first_name', 'last_name', 'phone', 'dob');
        /** @var Customer $customer */
        $customer = $request->user();
        $customer->update($attributes);

        return new CustomerResource($customer);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): Response
    {
        $request->validate([
            'current_password' => ['required', 'current_password', 'string'],
            'password' => ['required',  'string', Password::defaults()],
        ], [
            'current_password.current_password' => 'The provided password does not match your current password.',
        ]);

        /** @var Customer $customer */
        $customer = $request->user();
        $customer->update([
            'password' => Hash::make($request->string('password')->value()),
        ]);

        event(new PasswordReset($customer));

        return response()->noContent();
    }

    /**
     * Send a new email verification notification.
     */
    public function sendEmailVerificationNotification(Request $request): Response
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if ($customer->hasVerifiedEmail()) {
            return response()->noContent();
        }

        $customer->sendEmailVerificationNotification();

        return response()->noContent();
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verifyEmail(EmailVerificationRequest $request): Response
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if ($customer->hasVerifiedEmail()) {
            return response()->noContent();
        }

        if ($customer->markEmailAsVerified()) {
            event(new Verified($customer));
        }

        return response()->noContent();
    }

    /**
     * Send email change verification notification.
     */
    public function sendEmailChangeVerificationNotification(Request $request): Response
    {
        $request->validate([
            'email' => ['required', 'string', 'email', Rule::unique('users')],
        ]);

        /** @var Customer $customer */
        $customer = $request->user();

        Notification::route('mail', $request->string('email'))
            ->notify(new VerifyNewEmail($customer));

        return response()->noContent();
    }

    /**
     * Verify the new email address
     */
    public function verifyNewEmail(Request $request): Response
    {
        /** @var Customer $customer */
        $customer = $request->user();

        /** @phpstan-ignore-next-line */
        if (! hash_equals((string) $customer->getKey(), (string) $request->route('id'))) {
            abort(403);
        }

        if (! hash_equals(sha1($request->route('email')), (string) $request->route('hash'))) {
            abort(403);
        }

        $customer->update([
            'email' => $request->route('email'),
            'email_verified_at' => now(),
        ]);

        event(new Verified($customer));

        return response()->noContent();
    }
}
