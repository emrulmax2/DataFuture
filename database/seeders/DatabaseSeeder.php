<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            ApplicantUserSeeder::class,
            StatusSeeder::class,
            AcademicYearSeeder::Class,
            AddressSeeder::Class
        ]);
    }
}
