<?php

use App\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admins = ['bakugo', 'deku'];
        $password = app('hash')->make('password');
        
        foreach ($admins as $admin) {
            User::create([
                'username' => $admin,
                'password' => $password
            ]);
        }
    }
}
