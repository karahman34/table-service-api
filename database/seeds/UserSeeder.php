<?php

use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $usernames = ['admin', 'waiter', 'cashier', 'customer', 'owner'];
        $password = app('hash')->make('password');

        foreach ($usernames as $username) {
            $user = User::create([
                'username' => $username,
                'password' => $password
            ]);

            $user->roles()->sync([
                Role::select('id')->whereName($username)->firstOrFail()->id
            ]);
        }
    }
}
