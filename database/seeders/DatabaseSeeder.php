<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@district3.test'],
            ['name' => 'District Administrator', 'password' => 'password'],
        );

        $admin->forceFill(['role' => UserRole::DistrictAdmin])->save();

        $this->call(LandingPageSeeder::class);
    }
}
