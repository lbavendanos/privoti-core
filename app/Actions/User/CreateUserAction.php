<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateUserAction
{
    /**
     * Create a new user.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function handle(array $attributes): User
    {
        return DB::transaction(fn (): User => User::query()->create($attributes));
    }
}
