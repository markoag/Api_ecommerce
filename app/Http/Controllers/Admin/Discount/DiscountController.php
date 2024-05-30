<?php

namespace App\Http\Controllers\Admin\Discount;

use App\Http\Controllers\Controller;
use App\Http\Resources\Discount\DiscountCollection;
use App\Http\Resources\Discount\DiscountResource;
use App\Models\Discount\Discount;
use App\Models\Discount\DiscountBrand;
use App\Models\Discount\DiscountCategorie;
use App\Models\Discount\DiscountProduct;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        // $type_campaign = $request->type_campaign;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $discounts = Discount::FilterAdvanceDiscount($search, $start_date, $end_date)
            ->orderBy("id", "desc")
            ->paginate(10);

        return response()->json([
            "total" => $discounts->total(),
            "discounts" => DiscountCollection::make($discounts),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación para crear las campañas de descuento
        if ($request->discount_type == 1) { // Nivel de Productos
            foreach ($request->product_selected as $key => $product_select) {
                $EXISTS_DISCOUNT = Discount::where("type_campaign", $request->type_campaign)
                    ->where("discount_type", $request->discount_type)
                    ->whereHas("products", function ($query) use ($product_select) {
                        $query->where("product_id", $product_select["id"]);
                    })
                    ->where(function ($query) use ($request) {
                        $query->whereBetween("start_date", [$request->start_date, $request->end_date])
                            ->orWhereBetween("end_date", [$request->start_date, $request->end_date]);
                    })
                    ->where("state", 1)
                    ->first();

                if ($EXISTS_DISCOUNT) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "El producto " . $product_select["title"] . " ya tiene una campaña de descuento activa que sería: " . ($EXISTS_DISCOUNT ? '#' . $EXISTS_DISCOUNT->code : 'N/A'),
                    ]);
                }
            }
        }
        if ($request->discount_type == 2) { // Nivel de Categorías
            foreach ($request->categorie_selected as $key => $categorie_select) {
                $EXISTS_DISCOUNT = Discount::where("type_campaign", $request->type_campaign)
                    ->where("discount_type", $request->discount_type)
                    ->whereHas("categories", function ($query) use ($categorie_select) {
                        $query->where("categorie_id", $categorie_select["id"]);
                    })
                    ->where(function ($query) use ($request) {
                        $query->whereBetween("start_date", [$request->start_date, $request->end_date])
                            ->orWhereBetween("end_date", [$request->start_date, $request->end_date]);
                    })
                    ->where("state", 1)
                    ->first();

                if ($EXISTS_DISCOUNT) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "La categoría " . $categorie_select["name"] . " ya tiene una campaña de descuento activa que sería: " . ($EXISTS_DISCOUNT ? '#' . $EXISTS_DISCOUNT->code : 'N/A'),
                    ]);
                }
            }
        }
        if ($request->discount_type == 3) { // Nivel de Marcas
            foreach ($request->brand_selected as $key => $brand_select) {
                $EXISTS_DISCOUNT = Discount::where("type_campaign", $request->type_campaign)
                    ->where("discount_type", $request->discount_type)
                    ->whereHas("brands", function ($query) use ($brand_select) {
                        $query->where("brand_id", $brand_select["id"]);
                    })
                    ->where(function ($query) use ($request) {
                        $query->whereBetween("start_date", [$request->start_date, $request->end_date])
                            ->orWhereBetween("end_date", [$request->start_date, $request->end_date]);
                    })
                    ->where("state", 1)
                    ->first();

                if ($EXISTS_DISCOUNT) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "La marca " . $brand_select["name"] . " ya tiene una campaña de descuento activa que sería: " . ($EXISTS_DISCOUNT ? '#' . $EXISTS_DISCOUNT->code : 'N/A'),
                    ]);
                }
            }
        }

        $request->request->add(["code" => strtoupper(uniqid())]);
        $discount = Discount::create($request->all());

        foreach ($request->product_selected as $key => $product_select) {
            DiscountProduct::create([
                "discount_id" => $discount->id,
                "product_id" => $product_select["id"],
            ]);
        }
        foreach ($request->categorie_selected as $key => $categorie_select) {
            DiscountCategorie::create([
                "discount_id" => $discount->id,
                "categorie_id" => $categorie_select["id"],
            ]);
        }
        foreach ($request->brand_selected as $key => $brand_select) {
            DiscountBrand::create([
                "discount_id" => $discount->id,
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
        $discount = Discount::findOrFail($id);

        return response()->json([
            "discount" => DiscountResource::make($discount),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->discount_type == 1) {
            foreach ($request->product_selected as $key => $product_select) {
                $EXISTS_DISCOUNT = Discount::where("type_campaign", $request->type_campaign)
                    ->where("id", "<>", $id)
                    ->where("discount_type", $request->discount_type)
                    ->whereHas("products", function ($query) use ($product_select) {
                        $query->where("product_id", $product_select["id"]);
                    })
                    ->where(function ($query) use ($request) {
                        $query->whereBetween("start_date", [$request->start_date, $request->end_date])
                            ->orWhereBetween("end_date", [$request->start_date, $request->end_date]);
                    })
                    ->where("state", 1)
                    ->first();

                if ($EXISTS_DISCOUNT) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "El producto " . $product_select["title"] . " ya tiene una campaña de descuento activa que sería: " . ($EXISTS_DISCOUNT ? '#' . $EXISTS_DISCOUNT->code : 'N/A'),
                    ]);
                }
            }
        }
        if ($request->discount_type == 2) {
            foreach ($request->categorie_selected as $key => $categorie_select) {
                $EXISTS_DISCOUNT = Discount::where("type_campaign", $request->type_campaign)
                    ->where("id", "<>", $id)
                    ->where("discount_type", $request->discount_type)
                    ->whereHas("categories", function ($query) use ($categorie_select) {
                        $query->where("categorie_id", $categorie_select["id"]);
                    })
                    ->where(function ($query) use ($request) {
                        $query->whereBetween("start_date", [$request->start_date, $request->end_date])
                            ->orWhereBetween("end_date", [$request->start_date, $request->end_date]);
                    })
                    ->where("state", 1)
                    ->first();

                if ($EXISTS_DISCOUNT) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "La categoría " . $categorie_select["name"] . " ya tiene una campaña de descuento activa que sería: " . ($EXISTS_DISCOUNT ? '#' . $EXISTS_DISCOUNT->code : 'N/A'),
                    ]);
                }
            }
        }
        if ($request->discount_type == 3) {
            foreach ($request->brand_selected as $key => $brand_select) {
                $EXISTS_DISCOUNT = Discount::where("type_campaign", $request->type_campaign)
                    ->where("id", "<>", $id)
                    ->where("discount_type", $request->discount_type)
                    ->whereHas("brands", function ($query) use ($brand_select) {
                        $query->where("brand_id", $brand_select["id"]);
                    })
                    ->where(function ($query) use ($request) {
                        $query->whereBetween("start_date", [$request->start_date, $request->end_date])
                            ->orWhereBetween("end_date", [$request->start_date, $request->end_date]);
                    })
                    ->where("state", 1)
                    ->first();

                if ($EXISTS_DISCOUNT) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "La marca " . $brand_select["name"] . " ya tiene una campaña de descuento activa que sería: " . ($EXISTS_DISCOUNT ? '#' . $EXISTS_DISCOUNT->code : 'N/A'),
                    ]);
                }
            }
        }

        $discount = Discount::findOrFail($id);
        $discount->update($request->all());

        foreach ($discount->products as $key => $product) {
            $product->delete();
        }
        foreach ($discount->categories as $key => $categorie) {
            $categorie->delete();
        }
        foreach ($discount->brands as $key => $brand) {
            $brand->delete();
        }

        foreach ($request->product_selected as $key => $product_select) {
            DiscountProduct::create([
                "discount_id" => $discount->id,
                "product_id" => $product_select["id"],
            ]);
        }
        foreach ($request->categorie_selected as $key => $categorie_select) {
            DiscountCategorie::create([
                "discount_id" => $discount->id,
                "categorie_id" => $categorie_select["id"],
            ]);
        }
        foreach ($request->brand_selected as $key => $brand_select) {
            DiscountBrand::create([
                "discount_id" => $discount->id,
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
        $discount = Discount::findOrFail($id);
        $discount->delete();

        // Validar cuando haya una venta relacionada con el descuento
        return response()->json(["message" => 200]);
    }
}
