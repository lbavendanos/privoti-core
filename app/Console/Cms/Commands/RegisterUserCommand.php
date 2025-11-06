<?php

declare(strict_types=1);

namespace App\Console\Cms\Commands;

use App\Actions\User\RegisterUserAction;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class RegisterUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:user:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a new user.';

    /**
     * Execute the console command.
     */
    public function handle(RegisterUserAction $action): void
    {
        $name = $this->ask('What is your name?');
        $email = $this->ask('What is your email?');
        $password = $this->secret('What is your password?');
        $passwordConfirmation = $this->secret('Please confirm your password');

        $validator = validator([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            $this->fail('User creation failed due to validation errors.');
        }

        /** @var array<string,mixed> $attributes */
        $attributes = $validator->validated();
        $action->handle($attributes);

        $this->info('User created successfully!');
    }
}
