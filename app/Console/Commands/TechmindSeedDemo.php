<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Console\Command;

class TechmindSeedDemo extends Command
{
    protected $signature = 'techmind:seed-demo';

    protected $description = 'Menyiapkan data referensi dan 18 sesi demo untuk pameran TechMind';

    public function handle(): int
    {
        $this->call(ReferenceDataSeeder::class);
        $this->call(DemoSeeder::class);
        $this->info('Data demo TechMind siap digunakan.');

        return self::SUCCESS;
    }
}
