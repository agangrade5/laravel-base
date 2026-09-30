<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            [
                'email' => 'super_admin@mailinator.com',
            ],
            [
                'name' => 'Administrator',
                'password' => 'Admin@123',
            ]
        );

        $admin->syncRoles(['admin']);
    }
}
