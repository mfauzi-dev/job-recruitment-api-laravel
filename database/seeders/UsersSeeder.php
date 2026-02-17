<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect([
            [
                'name' => 'fauzi admin',
                'role_as' => 'admin',
                'email' => 'fauziadmin@job-recruitment.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ])->each(function ($user) {
            DB::table('users')->insert($user);
        });
    }
}
