<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PcDevicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $devices = [];
        for ($i = 0; $i < 60; $i++) {
            $devices[] = [
                'status' => 'vacant',
                'pc_assignments_id' => null,
                'device_name' => 'PC-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'mac_address' => null,
                'user_id' => null,
                'start_time' => null,
                'end_time' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('pc_devices')->insert($devices);
    }

    private function generateMacAddress(): string
    {
        return implode(':', array_map(
            fn () => sprintf('%02X', rand(0, 255)),
            range(1, 6)
        ));
    }
}
