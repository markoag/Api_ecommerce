<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\Product\ProductEcommerceCollection;
use App\Http\Resources\Ecommerce\Product\ProductEcommerceResource;
use App\Models\Discount\Discount;
use App\Models\Product\Categorie;
use App\Models\Product\Product;
use App\Models\Slider;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home(Request $request)
    {
        $slider_principal = Slider::where("state", 1)->where("type", 1)->orderBy("id", "desc")->get();
        $slider_secundario = Slider::where("state", 1)->where("type", 2)->orderBy("id", "asc")->get();
        $slider_products = Slider::where("state", 1)->where("type", 3)->orderBy("id", "asc")->get();

        $categories_randoms = Categorie::withCount(["product_categorie_first"])
            ->where("categorie_second_id", null)->where("categorie_third_id", null)
            ->inRandomOrder()->limit(5)->get();
        $category_first = Categorie::withCount(["product_categorie_first"])
            ->where("categorie_second_id", null)->where("categorie_third_id", null)
            ->limit(1)->get();

        $product_trending_new = Product::where("state", 2)->inRandomOrder()->limit(8)->get();
        $product_trending_featured = Product::where("state", 2)->inRandomOrder()->limit(8)->get();
        $product_trending_top_sellers = Product::where("state", 2)->inRandomOrder()->limit(8)->get();
        $product_category_first = Product::where("state", 2)->where("categorie_first_id", 1)->inRandomOrder()->limit(6)->get();
        $product_carousel = Product::where("state", 2)->whereIn("categorie_first_id", $categories_randoms->pluck("id"))->inRandomOrder()->get();

        // Obtenemos el descuento flash
        date_default_timezone_set("America/Guayaquil");
        $DISCOUNT_FLASH = Discount::where("type_campaign", 2)
            ->where("state", 1)
            ->where("start_date", "<=", now())
            ->where("end_date", ">=", now())
            ->first();

            // echo now();
        
        $DISCOUNT_FLASH_PRODUCTS = collect([]);

        if ($DISCOUNT_FLASH) {
            foreach ($DISCOUNT_FLASH->products as $aux_product) {
                $DISCOUNT_FLASH_PRODUCTS->push(ProductEcommerceResource::make($aux_product->product));
            }
            foreach ($DISCOUNT_FLASH->categories as $aux_category) {
                $products_of_categories = Product::where("state",2)->where("categorie_first_id", $aux_category->categorie_id)->get();
                foreach ($products_of_categories as $product) {
                    $DISCOUNT_FLASH_PRODUCTS->push(ProductEcommerceResource::make($product));
                }
            }
            foreach ($DISCOUNT_FLASH->brands as $aux_brand) {
                $products_of_brands = Product::where("state",2)->where("brand_id", $aux_brand->brand_id)->get();
                foreach ($products_of_brands as $product) {
                    $DISCOUNT_FLASH_PRODUCTS->push(ProductEcommerceResource::make($product));
                }
            }
            // Sep 30 2024 20:20:22
            $DISCOUNT_FLASH->end_date_format = Carbon::parse($DISCOUNT_FLASH->end_date)->format("M d Y H:i:s");
        }

        return response()->json([
            "slider_principal" => $slider_principal->map(function ($slider) {
                return [
                    "id" => $slider->id,
                    "title" => $slider->title,
                    "label" => $slider->label,
                    "type" => $slider->type,
                    "subtitle" => $slider->subtitle,
                    "image" => $slider->image ? env("APP_URL") . "storage/" . $slider->image : null,
                    "link" => $slider->link,
                    "color" => $slider->color,
                    "state" => $slider->state,
                ];
            }),
            "slider_secundario" => $slider_secundario->map(function ($slider) {
                return [
                    "id" => $slider->id,
                    "title" => $slider->title,
                    "label" => $slider->label,
                    "type" => $slider->type,
                    "type_view" => $slider->type_view,
                    "subtitle" => $slider->subtitle,
                    "image" => $slider->image ? env("APP_URL") . "storage/" . $slider->image : null,
                    "link" => $slider->link,
                    "color" => $slider->color,
                    "state" => $slider->state,
                ];
            }),
            "slider_products" => $slider_products->map(function ($slider) {
                return [
                    "id" => $slider->id,
                    "title" => $slider->title,
                    "label" => $slider->label,
                    "type" => $slider->type,
                    "subtitle" => $slider->subtitle,
                    "image" => $slider->image ? env("APP_URL") . "storage/" . $slider->image : null,
                    "image2" => $slider->image2 ? env("APP_URL") . "storage/" . $slider->image2 : null,
                    "link" => $slider->link,
                    "color" => $slider->color,
                    "original_price" => $slider->original_price,
                    "campaign_price" => $slider->campaign_price,
                    "state" => $slider->state,
                ];
            }),
            "categories_random" => $categories_randoms->map(function ($category) {
                return [
                    "id" => $category->id,
                    "name" => $category->name,
                    "products_count" => $category->product_categorie_first_count,
                    "image" => env("APP_URL") . "storage/" . $category->image,
                ];
            }),
            "product_trending_new" => ProductEcommerceCollection::make($product_trending_new),
            "product_trending_featured" => ProductEcommerceCollection::make($product_trending_featured),
            "product_trending_top_sellers" => ProductEcommerceCollection::make($product_trending_top_sellers),
            "product_category_first" => ProductEcommerceCollection::make($product_category_first),
            "category_first" => $category_first->map(function ($category) {
                return [
                    "id" => $category->id,
                    "name" => $category->name,
                ];
            }),
            "product_carousel" => ProductEcommerceCollection::make($product_carousel),
            "discount_flash" => $DISCOUNT_FLASH,
            "discount_flash_products" => $DISCOUNT_FLASH_PRODUCTS,
        ]);
    }

    public function menus()
    {
        $categories_menu = Categorie::where("categorie_second_id", null)
            ->where("categorie_third_id", null)
            ->orderBy("position", "desc")
            ->get();

        return response()->json([

            "categories_menu" => $categories_menu->map(function ($departament) {
                return [
                    "id" => $departament->id,
                    "name" => $departament->name,
                    "icon" => $departament->icon,
                    "categories" => $departament->categorie_seconds->map(function ($category) {
                        return [
                            "id" => $category->id,
                            "name" => $category->name,
                            "image" => $category->image ? env("APP_URL") . "storage/" . $category->image : null,
                            "subcategories" => $category->categorie_seconds->map(function ($subcategory) {
                                return [
                                    "id" => $subcategory->id,
                                    "name" => $subcategory->name,
                                    "image" => $subcategory->image ? env("APP_URL") . "storage/" . $subcategory->image : null,
                                ];
                            }),
                        ];
                    }),
                ];
            }),
        ]);
    }
}
