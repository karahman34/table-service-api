<?php

use App\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $foods = collect([
            [
                'name' => 'nasi goreng seafood',
                'price' => 20000,
                'description' => 'example description text 1',
                'discount' => 0,
                'image' => Food::$image_folder . '/' . 'nasi_goreng.jpg',
                'category_id' => 8,
            ],
            [
                'name' => 'steak sapi',
                'price' => 35000,
                'description' => 'example description steak sapi',
                'discount' => 0,
                'image' => Food::$image_folder . '/' . 'steak_sapi.jpg',
                'category_id' => 8,
            ],
            [
                'name' => 'gurame saus asam manis',
                'price' => 50000,
                'description' => 'example description gurame',
                'discount' => 0,
                'image' => Food::$image_folder . '/' . 'gurame.jpg',
                'category_id' => 8,
            ],
            [
                'name' => 'es kelapa muda',
                'price' => 5000,
                'description' => 'example description kelapa muda',
                'discount' => 0,
                'image' => Food::$image_folder . '/' . 'es_kelapa_muda.jpg',
                'category_id' => 7,
            ],
            [
                'name' => 'mie goreng jawa',
                'price' => 15000,
                'description' => 'example description mie goreng',
                'discount' => 0,
                'image' => Food::$image_folder . '/' . 'mie_goreng_jawa.jpg',
                'category_id' => 4,
            ],
        ]);

        $foods->each(function ($food) {
            Food::create($food);
        });
    }
}
