<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        $this->call('RolesPermissionsSeeder');
        $this->call('UserSeeder');
        $this->call('CategorySeeder');
        $this->call('TableSeeder');
        $this->call('FoodSeeder');
    }
}
