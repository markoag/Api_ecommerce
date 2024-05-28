<?php

namespace App\Http\Controllers\Admin\Coupon;

use App\Http\Controllers\Controller;
use App\Http\Resources\Coupon\CouponCollection;
use App\Http\Resources\Coupon\CouponResource;
use App\Models\Coupon\Coupon;
use App\Models\Coupon\CouponBrand;
use App\Models\Coupon\CouponCategorie;
use App\Models\Coupon\CouponProduct;
use App\Models\Product\Brand;
use App\Models\Product\Categorie;
use App\Models\Product\Product;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $coupons = Coupon::where("code", "like", "%" . $request->search . "%")->orderBy("id", "desc")->paginate(10);
        return response()->json([
            "total" => $coupons->total(),
            "coupons" => CouponCollection::make($coupons),
        ]);
    }

    public function config()
    {
        $products = Product::where("state", 2)->orderBy("id", "desc")->get();
        $categories = Categorie::where("state", 1)
            ->where("categorie_second_id", null)
            ->where("categorie_third_id", null)
            ->orderBy("id", "desc")->get();
        $brands = Brand::where("state", 1)->orderBy("id", "desc")->get();

        return response()->json([
            "products" => $products->map(function ($product) {
                return [
                    "id" => $product->id,
                    "title" => $product->title,
                    "image" => env("APP_URL")."storage/".$product->image,
                ];
            }),
            "categories" => $categories->map(function ($categorie) {
                return [
                    "id" => $categorie->id,
                    "name" => $categorie->name,
                    "image" => env("APP_URL")."storage/".$categorie->image,
                ];
            }),
            "brands" => $brands->map(function ($brand) {
                return [
                    "id" => $brand->id,
                    "name" => $brand->name,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // product_selected, categorie_selected, brand_selected
        $IS_EXIST = Coupon::where("code", $request->code)->first();
        if ($IS_EXIST) {
            return response()->json([
                "message" => 403,
                "message_text" => "El código de cupón ya existe, por favor ingrese otro.",
            ]);
        }

        $COUPON = Coupon::create($request->all());

        foreach ($request->product_selected as $key => $product_select) {
            CouponProduct::create([
                "coupon_id" => $COUPON->id,
                "product_id" => $product_select["id"],
            ]);
        }
        foreach ($request->categorie_selected as $key => $categorie_select) {
            CouponCategorie::create([
                "coupon_id" => $COUPON->id,
                "categorie_id" => $categorie_select["id"],
            ]);
        }
        foreach ($request->brand_selected as $key => $brand_select) {
            CouponBrand::create([
                "coupon_id" => $COUPON->id,
                "brand_id" => $brand_select["id"],
            ]);
        }
        return response()->json(["message" => 200]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $COUPON = Coupon::findOrFail($id);

        return response()->json([
            "coupon" => CouponResource::make($COUPON)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // product_selected, categorie_selected, brand_selected
        $IS_EXIST = Coupon::where("code", $request->code)->where("id", "<>", $id)->first();
        if ($IS_EXIST) {
            return response()->json([
                "message" => 403,
                "message_text" => "El código de cupón ya existe, por favor ingrese otro.",
            ]);
        }

        $COUPON = Coupon::findOrFail($id);
        $COUPON->update($request->all());

        foreach ($COUPON->products as $key => $product) {
            $product->delete();
        }
        foreach ($COUPON->categories as $key => $categorie) {
            $categorie->delete();
        }
        foreach ($COUPON->brands as $key => $brand) {
            $brand->delete();
        }

        foreach ($request->product_selected as $key => $product_select) {
            CouponProduct::create([
                "coupon_id" => $COUPON->id,
                "product_id" => $product_select["id"],
            ]);
        }
        foreach ($request->categorie_selected as $key => $categorie_select) {
            CouponCategorie::create([
                "coupon_id" => $COUPON->id,
                "categorie_id" => $categorie_select["id"],
            ]);
        }
        foreach ($request->brand_selected as $key => $brand_select) {
            CouponBrand::create([
                "coupon_id" => $COUPON->id,
                "brand_id" => $brand_select["id"],
            ]);
        }
        return response()->json(["message" => 200]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $COUPON = Coupon::findOrFail($id);
        $COUPON->delete();

        // Validar cuando haya una venta relacionada con el cupón
        return response()->json(["message" => 200]);
    }
}
