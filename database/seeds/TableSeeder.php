<?php

use App\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($number = 1; $number <= 20; $number++) {
            Table::create([
                'number' => $number,
                'available' => 'Y',
            ]);
        }
    }
}
