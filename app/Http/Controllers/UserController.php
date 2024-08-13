<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return new UserResource($request->user());
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

        return new UserResource($request->user());
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
}
