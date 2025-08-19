<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            [
                'device_id' => 1,
                'device_name' => 'Security Camera 1',
                'location' => 'Front Gate',
                'status' => 'offline',
            ],
            [
                'device_id' => 2,
                'device_name' => 'Security Camera 2',
                'location' => 'Back Yard',
                'status' => 'offline',
            ],
            [
                'device_id' => 3,
                'device_name' => 'Security Camera 3',
                'location' => 'Side Entrance',
                'status' => 'offline',
            ],
            [
                'device_id' => 4,
                'device_name' => 'Security Camera 4',
                'location' => 'Parking Area',
                'status' => 'offline',
            ],
        ];

        foreach ($devices as $device) {
            Device::updateOrCreate(
                ['device_id' => $device['device_id']],
                $device
            );
        }

        $this->command->info('Devices seeded successfully!');
    }
}
