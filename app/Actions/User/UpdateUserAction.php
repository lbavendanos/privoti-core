<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class UpdateUserAction
{
    /**
     * Update the given user.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(User|int $user, array $attributes): User
    {
        return DB::transaction(function () use ($user, $attributes): User {
            $user = $user instanceof User ? $user : User::query()->findOrFail($user);

            $user->update($attributes);

            return $user;
        });
    }
}
