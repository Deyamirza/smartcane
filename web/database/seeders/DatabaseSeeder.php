<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Users
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@smartcane.com',
            'password_hash' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        $family = User::create([
            'username' => 'family',
            'email' => 'family@smartcane.com',
            'password_hash' => Hash::make('family123'),
            'role' => 'family',
        ]);

        // 2. Create Default Device
        Device::create([
            'id_user' => $admin->id_user,
            'device_name' => 'SMARTCANE-001',
            'mac_address' => 'A0:B1:C2:D3:E4:F5',
            'status' => 'active',
        ]);
    }
}
