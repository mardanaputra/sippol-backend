<?php

namespace Database\Seeders;

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
        $this->call([
            UserSeeder::class,
            PortalBidangSeeder::class,
        ]);

        $seedDummy = filter_var(env('SEED_DUMMY_DATA', false), FILTER_VALIDATE_BOOLEAN) || !app()->environment('production');
        if ($seedDummy) {
            $this->call([
                SippolDummySeeder::class,
            ]);
        }
    }
}
