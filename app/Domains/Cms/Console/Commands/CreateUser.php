<?php

namespace App\Domains\Cms\Console\Commands;

use App\Domains\Cms\Http\Controllers\Auth\AuthController;
use Illuminate\Console\Command;

class CreateUser extends Command
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
    public function handle()
    {
        $firstName = $this->ask('What is your first name?');
        $lastName = $this->ask('What is your last name?');
        $email = $this->ask('What is your email?');
        $password = $this->secret('What is your password?');
        $passwordConfirmation = $this->secret('Please confirm your password');

        $request = request()->merge([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ]);

        $controller = app()->make(AuthController::class);

        app()->call([$controller, 'register'], ['request' => $request]);

        $this->info('User created successfully!');
    }
}
