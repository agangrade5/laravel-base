<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = config('constants.settings', []);

        foreach ($settings as $type => $values) {
            Setting::updateOrCreate(
                ['type' => $type],
                [
                    'value' => json_encode($values),
                ]
            );
        }
    }
}
