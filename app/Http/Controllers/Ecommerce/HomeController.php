<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\Product\ProductEcommerceCollection;
use App\Http\Resources\Ecommerce\Product\ProductEcommerceResource;
use App\Models\Discount\Discount;
use App\Models\Product\Brand;
use App\Models\Product\Categorie;
use App\Models\Product\Product;
use App\Models\Product\Propertie;
use App\Models\Sale\Review;
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

        $DISCOUNT_FLASH_PRODUCTS = collect([]);

        if ($DISCOUNT_FLASH) {
            foreach ($DISCOUNT_FLASH->products as $aux_product) {
                $DISCOUNT_FLASH_PRODUCTS->push(ProductEcommerceResource::make($aux_product->product));
            }
            foreach ($DISCOUNT_FLASH->categories as $aux_category) {
                $products_of_categories = Product::where("state", 2)->where("categorie_first_id", $aux_category->categorie_id)->get();
                foreach ($products_of_categories as $product) {
                    $DISCOUNT_FLASH_PRODUCTS->push(ProductEcommerceResource::make($product));
                }
            }
            foreach ($DISCOUNT_FLASH->brands as $aux_brand) {
                $products_of_brands = Product::where("state", 2)->where("brand_id", $aux_brand->brand_id)->get();
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

    public function show_product(Request $request, $slug)
    {
        $campaign_discount = $request->get("campaign_discount");
        $discount = null;
        if ($campaign_discount) {
            $discount = Discount::where("code", $campaign_discount)->first();
        }

        $product = Product::where("slug", $slug)->where("state", 2)->first();

        if (!$product) {
            return response()->json([
                "message" => 403,
                "message_text" => "Producto no encontrado."
            ]);
        }

        $products_relateds = Product::where("categorie_first_id", $product->categorie_first_id)
            // ->where("categorie_second_id", $product->categorie_second_id)
            // ->where("categorie_third_id", $product->categorie_third_id)
            ->where("state", 2)
            ->where("id", "!=", $product->id)
            ->inRandomOrder()
            ->limit(8)
            ->get();

        $reviews = Review::where("product_id", $product->id)->get();

        return response()->json([
            "message" => 200,
            "product" => ProductEcommerceResource::make($product),
            "products_relateds" => ProductEcommerceCollection::make($products_relateds),
            "discount_campaign" => $discount,
            "reviews" => $reviews->map(function ($review) {
                return [
                    "id" => $review->id,
                    "user" => [
                        "full_name" => $review->user->name . ' ' . $review->user->last_name,
                        'avatar' => $review->user->avatar ? env('APP_URL') . 'storage/' . $review->user->avatar : 'https://cdn-icons-png.flaticon.com/512/1077/1077114.png',
                    ],
                    "message" => $review->message,
                    "rating" => $review->rating,
                    "created_at" => $review->created_at->format("M d Y H:i"),
                ];
            }),
        ]);
    }

    public function config_filter_advance()
    {
        $categories = Categorie::withCount(["product_categorie_first"])
            ->where("categorie_second_id", null)->where("categorie_third_id", null)
            ->get();

        $brands = Brand::withCount(["products"])->where("state", 1)
            ->whereHas("products", function ($query) {
                $query->where("state", 2);
            })
            ->orderBy("name", "asc")->get();
        // Validar que solo se presenten las marcas que tengan productos
        $brands = $brands->filter(function ($brand) {
            return $brand->products_count > 0;
        })->values();

        $colors = Propertie::where("code", "<>", null)
            ->whereHas('variations', function ($query) {
                $query->whereHas('product', function ($query) {
                    $query->where('state', 2);
                });
            })
            ->get();

        $colors = $colors->map(function ($color) {
            if ($color->attribute && $color->variations) {
                $color->products_count = $color->variations->unique("product_id")->count();
            } else {
                $color->products_count = 0; // O cualquier valor por defecto que consideres adecuado
            }
            return $color;
        })->filter(function ($color) {
            return $color->products_count > 0;
        })->values()->toArray();

        $products_relateds = Product::where("state", 2)->inRandomOrder()->limit(4)->get();

        return response()->json([
            "categories" => $categories->map(function ($category) {
                return [
                    "id" => $category->id,
                    "name" => $category->name,
                    "products_count" => $category->product_categorie_first_count,
                    "image" => env("APP_URL") . "storage/" . $category->image,
                ];
            }),
            "brands" => $brands->map(function ($brand) {
                return [
                    "id" => $brand->id,
                    "name" => $brand->name,
                    "products_count" => $brand->products_count,
                ];
            }),
            "colors" => $colors,
            "products_relateds" => ProductEcommerceCollection::make($products_relateds),
        ]);
    }
    public function filter_advance_product(Request $request)
    {

        $categories_selected = $request->categories_selected;
        $brand_selected = $request->brand_selected;
        $colors_selected = $request->colors_selected;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $price_view = $request->price_view;
        $options_aditionals = $request->options_aditionals;
        $search = $request->search;

        $colors_product_selected = [];
        if ($colors_selected && sizeof($colors_selected) > 0) {
            $properties = Propertie::whereIn("id", $colors_selected)->get();
            foreach ($properties as $property) {
                foreach ($property->variations as $variation) {
                    $colors_product_selected[] = $variation->product_id;
                }
            }
        }

        $product_general_ids_array = [];
        if ($options_aditionals && sizeof($options_aditionals) > 0 && in_array("campaing", $options_aditionals)) {

            date_default_timezone_set("America/Guayaquil");
            $discount = Discount::where("type_campaign", 1)
                ->where("state", 1)
                ->where("start_date", "<=", now())
                ->where("end_date", ">=", now())
                ->first();

            if ($discount) {
                foreach ($discount->products as $product_aux) {
                    array_push($product_general_ids_array, $product_aux->product_id);
                }
                foreach ($discount->categories as $category_aux) {
                    array_push($categories_selected, $category_aux->categorie_id);
                }
                foreach ($discount->brands as $brand_aux) {
                    array_push($brand_selected, $brand_aux->brand_id);
                }
            }
        }

        $products = Product::where("state", 2)->filterAdvanceEcommerce(
            $categories_selected,
            $colors_product_selected,
            $brand_selected,
            $min_price,
            $max_price,
            $price_view,
            $product_general_ids_array,
            $options_aditionals,
            $search
        )->orderBy("id", "desc")->get();

        return response()->json([
            "products" => ProductEcommerceCollection::make($products),
        ]);
    }
}
