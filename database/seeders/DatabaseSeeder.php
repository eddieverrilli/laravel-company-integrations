<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Integration;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with two companies, so you can sign in
     * as one and check that the other's pages are a 404.
     */
    public function run(): void
    {
        $acme = Company::factory()->create(['name' => 'Acme Analytics']);
        $globex = Company::factory()->create(['name' => 'Globex Media']);

        User::factory()->for($acme)->create(['name' => 'Ada Admin', 'email' => 'ada@acme.test']);
        User::factory()->for($acme)->create(['name' => 'Alan Analyst', 'email' => 'alan@acme.test']);
        User::factory()->for($globex)->create(['name' => 'Grace Admin', 'email' => 'grace@globex.test']);

        Integration::factory()->for($acme)->create(['name' => 'Search ads']);
        Integration::factory()->for($acme)->create(['name' => 'Call tracking']);
        Integration::factory()->for($globex)->create(['name' => 'Social ads']);
    }
}
