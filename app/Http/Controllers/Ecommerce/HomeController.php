<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\Product\ProductEcommerceCollection;
use App\Models\Product\Categorie;
use App\Models\Product\Product;
use App\Models\Slider;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home(Request $request)
    {
        $slider_principal = Slider::where("state", 1)->where("type", 1)->orderBy("id", "desc")->get();
        $slider_secundario = Slider::where("state", 1)->where("type", 2)->orderBy("id", "asc")->get();

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
                    "original_price" => $slider->original_price,
                    "campaign_price" => $slider->campaign_price,
                    "state" => $slider->state,
                ];
            }),
            "product_category_first" => ProductEcommerceCollection::make($product_category_first),
            "category_first" => $category_first->map(function ($category) {
                return [
                    "id" => $category->id,
                    "name" => $category->name,
                ];
            }),
            "product_carousel" => ProductEcommerceCollection::make($product_carousel),
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
