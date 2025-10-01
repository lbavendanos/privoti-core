<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class RegisterUserAction
{
    public function __construct(
        private CreateUserAction $createUserAction,
    ) {
        //
    }

    /**
     * Register a new user.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $user = $this->createUserAction->handle($attributes);

            $user->forceFill(['email_verified_at' => now()])->save();

            return $user;
        });
    }
}
