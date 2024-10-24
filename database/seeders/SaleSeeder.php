<?php

namespace Database\Seeders;

use App\Models\Address\City;
use App\Models\Address\Parish;
use App\Models\Address\Province;
use App\Models\Coupon\Coupon;
use App\Models\Discount\Discount;
use App\Models\Product\Product;
use App\Models\Sale\Sale;
use App\Models\Sale\SaleAddres;
use App\Models\Sale\SaleDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sale::factory()->count(1000)->create()->each(function ($p) {
            $faker = \Faker\Factory::create();

            $province = Province::inRandomOrder()->first();
            if (!$province) {
                return; // Si no hay provincias, salta esta iteración
            }

            $city = City::where('province_id', $province->id)->inRandomOrder()->first();
            if (!$city) {
                return; // Si no hay ciudades para la provincia, salta esta iteración
            }

            $parish = Parish::where('city_id', $city->code)->inRandomOrder()->first();
            if (!$parish) {
                return; // Si no hay parroquias para la ciudad, salta esta iteración
            }

            SaleAddres::create([
                "sale_id" => $p->id,
                "province_id" => $province->id,
                "city_id" => $city->code,
                "parish_id" => $parish->code,
                "company" =>  $faker->word(),
                "main_street" =>  $faker->word(),
                "secondary_street" =>  $faker->word(),
                "reference" =>  $faker->word(),
                "sector" =>  $faker->word(),
                "house_number" => Str::random(4),
            ]);

            $num_items = $faker->randomElement([1, 2, 3, 4, 5]);

            $sum_total_sale = 0;
            for ($i = 0; $i < $num_items; $i++) {
                $quantity = $faker->randomElement([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
                $product = Product::where('state', 2)->inRandomOrder()->first();
                $is_cupon_discount = $faker->randomElement([1, 2, 3]);
                $discount_cupone = $this->getDiscountCupone($is_cupon_discount);
                $sale_detail = SaleDetail::create([
                    "sale_id" => $p->id,
                    "product_id" => $product->id,
                    "type_discount" => $discount_cupone ? $discount_cupone->type_discount : NULL,
                    "discount" => $discount_cupone ? $discount_cupone->discount : NULL,
                    "type_campaign" => $is_cupon_discount == 2 ? $discount_cupone->type_campaing : NULL,
                    "code_coupon" => $is_cupon_discount == 1 ? $discount_cupone->code : NULL,
                    "code_discount" => $is_cupon_discount == 2 ? $discount_cupone->code : NULL,
                    "product_variation_id" => NULL,
                    "quantity" => $quantity,
                    "price_unit" => $product->price_desc,
                    "subtotal" => $this->getTotalProduct($discount_cupone, $product),
                    "total" => $this->getTotalProduct($discount_cupone, $product) * $quantity,
                    "created_at" => $p->created_at,
                    "updated_at" => $p->updated_at,
                ]);
                $sum_total_sale += $sale_detail->total;
            }

            $sale = Sale::findOrFail($p->id);
            $sale->update([
                "subtotal" => $sum_total_sale,
                "total" => $sum_total_sale,
            ]);
        });
        // php artisan db:seed --class=SaleSeeder
    }

    public function getDiscountCupone($is_cupon_discount)
    {
        if ($is_cupon_discount != 3) {
            if ($is_cupon_discount == 1) {
                $cupone = Coupon::inRandomOrder()->first();
                return $cupone;
            } else {
                $discount = Discount::inRandomOrder()->first();
                return $discount;
            }
        }
        return null;
    }

    public function getTotalProduct($discount_cupone, $product)
    {
        if ($discount_cupone) {

            $price = $product->price_desc;

            if ($discount_cupone->type_discount == 1) {
                $price = $price - $discount_cupone->discount * 0.01 * $price;
            } else {
                $price = $price - $discount_cupone->discount;
            }
            return $price;
        }

        return $product->price_desc;
    }
}
