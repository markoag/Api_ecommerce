<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Attribute;
use App\Models\Product\ProductVariation;
use Illuminate\Http\Request;

class ProductVariationsNestedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product_id = request()->product_id;
        $product_variation_id = request()->product_variation_id;

        $variations = ProductVariation::where("product_id", $product_id)
            ->where("product_variation_id", $product_variation_id)
            ->orderBy("id", "desc")->get();

        return response()->json([
            "variations" => $variations->map(function ($variation) {
                return [
                    "id" => $variation->id,
                    "product_id" => $variation->product_id,
                    "attribute_id" => $variation->attribute_id,
                    "attribute" => $variation->attribute ? [
                        "name" => $variation->attribute->name,
                        "type_attribute" => $variation->attribute->type_attribute,
                    ] : null,
                    "propertie_id" => $variation->propertie_id,
                    "propertie" => $variation->propertie ? [
                        "name" => $variation->propertie->name,
                        "code" => $variation->propertie->code,
                    ] : null,
                    "value_add" => $variation->value_add,
                    "price_add" => $variation->price_add,
                    "stock" => $variation->stock,
                    "product_variation_id" => $variation->product_variation_id,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $variations_exist = ProductVariation::where("product_id", $request->product_id)
            ->where("product_variation_id", $request->product_variation_id)
            ->count();
        if ($variations_exist > 0) {
            $variations_attributes_exist = ProductVariation::where("product_id", $request->product_id)
                ->where("product_variation_id", $request->product_variation_id)
                ->where("attribute_id", $request->attribute_id)
                ->count();
            if ($variations_attributes_exist == 0) {
                return response()->json([
                    "message" => 403,
                    "message_text" => "No se puede agregar una variación con un atributo diferente al de las variaciones existentes",
                ]);
            }
        }
        $query = ProductVariation::where("product_id", $request->product_id)
            ->where("product_variation_id", $request->product_variation_id)
            ->where("attribute_id", $request->attribute_id);

        if ($request->propertie_id) {
            $query = $query->where("propertie_id", $request->propertie_id);
        } else {
            $query = $query->where("value_add", $request->value_add);
        }

        $is_valid_variation = $query->first();

        if ($is_valid_variation) {
            return response()->json([
                "message" => 403,
                "message_text" => "Ya existe una variación con estos datos",
            ]);
        }

        $product_var = ProductVariation::find($request->product_variation_id);
        $TOTAL_STOCK_VARIATION = $product_var ? $product_var->stock : 0;

        $SUM_TOTAL_STOCK_NESTED = ProductVariation::where("product_id", $request->product_id)
            ->where("product_variation_id", $request->product_variation_id)
            ->sum("stock");
        $SUM_TOTAL_STOCK_NESTED += $request->stock;

        if ($SUM_TOTAL_STOCK_NESTED > $TOTAL_STOCK_VARIATION) {
            return response()->json([
                "message" => 403,
                "message_text" => "El stock de las variaciones anidadas no puede ser mayor al stock de la variación padre",
            ]);
        }

        $product_variation = ProductVariation::create($request->all());

        return response()->json([
            "message" => 200,
            "variation" => [
                "id" => $product_variation->id,
                "product_id" => $product_variation->product_id,
                "attribute_id" => $product_variation->attribute_id,
                "attribute" => $product_variation->attribute ? [
                    "name" => $product_variation->attribute->name,
                    "type_attribute" => $product_variation->attribute->type_attribute,
                ] : null,
                "propertie_id" => $product_variation->propertie_id,
                "propertie" => $product_variation->propertie ? [
                    "name" => $product_variation->propertie->name,
                    "code" => $product_variation->propertie->code,
                ] : null,
                "value_add" => $product_variation->value_add,
                "price_add" => $product_variation->price_add,
                "stock" => $product_variation->stock,
                "product_variation_id" => $product_variation->product_variation_id,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $variations_exist = ProductVariation::where("product_id", $request->product_id)
            ->where("product_variation_id", $request->product_variation_id)
            ->count();
        if ($variations_exist > 0) {
            $variations_attributes_exist = ProductVariation::where("product_id", $request->product_id)
                ->where("product_variation_id", $request->product_variation_id)
                ->where("attribute_id", $request->attribute_id)
                ->count();
            if ($variations_attributes_exist == 0) {
                return response()->json([
                    "message" => 403,
                    "message_text" => "No se puede agregar una variación con un atributo diferente al de las variaciones existentes",
                ]);
            }
        }
        $query = ProductVariation::where("product_id", $request->product_id)
            ->where("product_variation_id", $request->product_variation_id)
            ->where("attribute_id", $request->attribute_id)
            ->where("id", "<>", $id);

        if ($request->propertie_id) {
            $query = $query->where("propertie_id", $request->propertie_id);
        } else {
            $query = $query->where("value_add", $request->value_add);
        }

        $is_valid_variation = $query->first();

        if ($is_valid_variation) {
            return response()->json([
                "message" => 403,
                "message_text" => "Ya existe una variación con estos datos",
            ]);
        }

        $product_var = ProductVariation::find($request->product_variation_id);
        $TOTAL_STOCK_VARIATION = $product_var ? $product_var->stock : 0;

        $SUM_TOTAL_STOCK_NESTED = ProductVariation::where("product_id", $request->product_id)
            ->where("id", "<>", $id)
            ->where("product_variation_id", $request->product_variation_id)
            ->sum("stock");
        $SUM_TOTAL_STOCK_NESTED += $request->stock;

        if ($SUM_TOTAL_STOCK_NESTED > $TOTAL_STOCK_VARIATION) {
            return response()->json([
                "message" => 403,
                "message_text" => "El stock de las variaciones anidadas no puede ser mayor al stock de la variación padre",
            ]);
        }

        $product_variation = ProductVariation::findOrFail($id);
        $product_variation->update($request->all());

        return response()->json([
            "message" => 200,
            "variation" => [
                "id" => $product_variation->id,
                "product_id" => $product_variation->product_id,
                "attribute_id" => $product_variation->attribute_id,
                "attribute" => $product_variation->attribute ? [
                    "name" => $product_variation->attribute->name,
                    "type_attribute" => $product_variation->attribute->type_attribute,
                ] : null,
                "propertie_id" => $product_variation->propertie_id,
                "propertie" => $product_variation->propertie ? [
                    "name" => $product_variation->propertie->name,
                    "code" => $product_variation->propertie->code,
                ] : null,
                "value_add" => $product_variation->value_add,
                "price_add" => $product_variation->price_add,
                "stock" => $product_variation->stock,
                "product_variation_id" => $product_variation->product_variation_id,
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product_variation = ProductVariation::findOrFail($id);
        $product_variation->delete();
        // Validacion para no eliminar en caso de que el producto o variacion este en el carrito o detallado en una orden
        return response()->json([
            "message" => 200,
        ]);
    }
}
