<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:make {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (!$user) {
        $this->error("Пользователь с email {$email} не найден.");
        return self::FAILURE;
    }

    $user->forceFill(['role' => 'admin'])->save();
    $this->info("Пользователь {$email} теперь администратор.");

    return self::SUCCESS;
})->purpose('Grant administrator role to an existing user');
