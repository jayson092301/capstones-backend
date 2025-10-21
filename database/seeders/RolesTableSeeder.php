<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Roles;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $roles = ['student', 'faculty', 'staff'];

        foreach ($roles as $role) {
            Roles::create([
                'name' => $role,
                'isInactive' => false,
            ]);
        }
    }
}
