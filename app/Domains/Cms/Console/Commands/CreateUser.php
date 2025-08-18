<?php

declare(strict_types=1);

namespace App\Domains\Cms\Console\Commands;

use App\Domains\Cms\Http\Controllers\AuthController;
use Illuminate\Console\Command;

final class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->ask('What is your name?');
        $email = $this->ask('What is your email?');
        $password = $this->secret('What is your password?');
        $passwordConfirmation = $this->secret('Please confirm your password');

        $request = request()->merge([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ]);

        $controller = app()->make(AuthController::class);

        app()->call($controller->register(...), ['request' => $request]);

        $this->info('User created successfully!');
    }
}
