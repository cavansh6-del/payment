<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run()
    {
        // افزودن محصولات نمونه
        Product::create([
            'name' => 'Product 1',
            'description' => 'Description for Product 1',
            'price' => 100.00,
            'image_url' => 'https://example.com/images/product1.jpg',
            'published' => true,
            'order' => 1,
        ]);

        Product::create([
            'name' => 'Product 2',
            'description' => 'Description for Product 2',
            'price' => 150.00,
            'image_url' => 'https://example.com/images/product2.jpg',
            'published' => true,
            'order' => 2,
        ]);

        Product::create([
            'name' => 'Product 3',
            'description' => 'Description for Product 3',
            'price' => 200.00,
            'image_url' => 'https://example.com/images/product3.jpg',
            'published' => false,
            'order' => 3,
        ]);

        Product::create([
            'name' => 'Product 4',
            'description' => 'Description for Product 4',
            'price' => 250.00,
            'image_url' => 'https://example.com/images/product4.jpg',
            'published' => true,
            'order' => 4,
        ]);

    }
}
