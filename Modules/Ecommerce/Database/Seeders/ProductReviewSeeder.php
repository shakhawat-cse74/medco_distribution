<?php

namespace Modules\Ecommerce\Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Faker\Factory as Faker;
use Modules\Ecommerce\Entities\ProductReview;

class ProductReviewSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();
        $faker = Faker::create('en_US'); // Force English

        // Get all online products
        $products = Product::where('is_online', 1)->get();

        foreach ($products as $product) {
            $reviewCount = rand(10, 20);

           for ($i = 0; $i < $reviewCount; $i++) {
                ProductReview::create([
                    'product_id'    => $product->id,
                    'customer_id'   => rand(1, 50),
                    'customer_name' => $faker->name, // English name
                    'rating'        => rand(1, 5),
                    'review'        => $faker->sentence(rand(10, 20)), // English sentence
                    'approved'      => $faker->boolean(80),
                    'created_at'    => $faker->dateTimeBetween('-6 months', 'now'), // random last 6 months
                    'updated_at'    => $faker->dateTimeBetween('-6 months', 'now'), // optional
                ]);
            }
        }
    }
}
