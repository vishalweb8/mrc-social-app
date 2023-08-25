<?php

use Illuminate\Database\Seeder;
use App\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate(
            [
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('12345678'),
                'phone' => '9879876767'
            ]);
    }
}
