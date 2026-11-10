<?php

use App\Models\User;
use App\Tenancy\ProvisionTenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Console\Command\Command;

Artisan::command('crm:create-user', function () {
    $name = $this->ask('Name');
    $email = Str::lower(trim((string) $this->ask('Email')));
    $password = $this->secret('Password', false);
    $password_confirmation = $this->secret('Confirm password', false);

    $data = Validator::make(compact('name', 'email', 'password', 'password_confirmation'), [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'confirmed', 'max:1024', Password::min(12)],
    ])->validate();

    $user = User::create(collect($data)->only(['name', 'email', 'password'])->all());
    $this->info("Created user #{$user->id}.");

    return Command::SUCCESS;
})->purpose('Create a CRM login through the trusted console');

Artisan::command('crm:create-tenant {owner : Existing user ID} {slug} {name}', function (ProvisionTenant $provision) {
    $owner = User::find($this->argument('owner'));

    if ($owner === null) {
        $this->error('No user has that ID.');

        return Command::FAILURE;
    }

    $tenantId = $provision($this->argument('name'), $this->argument('slug'), $owner);
    $this->info("Created workspace #{$tenantId}.");

    return Command::SUCCESS;
})->purpose('Provision a tenant and assign its first owner');
