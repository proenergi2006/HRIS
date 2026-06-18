<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Admin HRD',
                'email'    => 'admin@proenergi.co.id',
                'password' => bcrypt('password'),
                'role'     => 'admin',
            ],
            [
                'name'     => 'Evaluator',
                'email'    => 'evaluator@proenergi.co.id',
                'password' => bcrypt('password'),
                'role'     => 'evaluator',
            ],
            [
                'name'     => 'User II',
                'email'    => 'user2@proenergi.co.id',
                'password' => bcrypt('password'),
                'role'     => 'user_ii',
            ],
            [
                'name'     => 'CFO',
                'email'    => 'cfo@proenergi.co.id',
                'password' => bcrypt('password'),
                'role'     => 'cfo',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => $data['password']]
            );
            $user->syncRoles([$data['role']]);
        }
    }
}
