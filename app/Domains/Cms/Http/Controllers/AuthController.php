<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Controllers;

use App\Domains\Cms\Http\Requests\Auth\LoginRequest;
use App\Domains\Cms\Http\Resources\UserResource;
use App\Domains\Cms\Notifications\VerifyNewEmail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    /**
     * Display the authenticated user.
     */
    public function getUser(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Update the authenticated user.
     */
    public function updateUser(Request $request): UserResource
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
        ]);

        $request->user()->update($request->only('name', 'phone', 'dob'));

        return new UserResource($request->user());
    }

    /**
     * Update the authenticated user's password.
     */
    public function updateUserPassword(Request $request): Response
    {
        $request->validate([
            'current_password' => ['required', 'current_password', 'string'],
            'password' => ['required',  'string', Rules\Password::defaults()],
        ], [
            'current_password.current_password' => 'The provided password does not match your current password.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->string('password')),
        ]);

        event(new PasswordReset($request->user()));

        return response()->noContent();
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function register(Request $request): JsonResource
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        return new UserResource($user);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): JsonResource
    {
        $request->authenticate();

        $request->session()->regenerate();

        return new UserResource(Auth::guard('cms')->user());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('cms')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        Password::setDefaultDriver('users');

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['message' => __($status)]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function resetPassword(Request $request): JsonResource
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        Password::setDefaultDriver('users');

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request): void {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                Auth::guard('cms')->login($user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return new UserResource(Auth::guard('cms')->user());
    }

    /**
     * Send a new email verification notification.
     */
    public function sendEmailVerificationNotification(Request $request): Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->noContent();
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->noContent();
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verifyEmail(EmailVerificationRequest $request): Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->noContent();
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
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

        Notification::route('mail', $request->string('email'))
            ->notify(new VerifyNewEmail($request->user()));

        return response()->noContent();
    }

    /**
     * Verify the new email address
     */
    public function verifyNewEmail(Request $request): Response
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
