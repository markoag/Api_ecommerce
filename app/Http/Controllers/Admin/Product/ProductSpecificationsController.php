<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductSpecification;
use Illuminate\Http\Request;

class ProductSpecificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product_id = request()->product_id;

        $specifications = ProductSpecification::where("product_id", $product_id)->orderBy("id", "desc")->get();

        return response()->json([
            "specifications" => $specifications->map(function ($specification) {
                return [
                    "id" => $specification->id,
                    "product_id" => $specification->product_id,
                    "attribute_id" => $specification->attribute_id,
                    "attribute" => $specification->attribute ? [
                        "name" => $specification->attribute->name,
                        "type_attribute" => $specification->attribute->type_attribute,
                    ] : null,
                    "propertie_id" => $specification->propertie_id,
                    "propertie" => $specification->propertie ? [
                        "name" => $specification->propertie->name,
                        "code" => $specification->propertie->code,
                    ] : null,
                    "value_add" => $specification->value_add,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $query = ProductSpecification::where("product_id", $request->product_id)
            ->where("attribute_id", $request->attribute_id);

        if ($request->propertie_id) {
            $query = $query->where("propertie_id", $request->propertie_id);
        } else {
            $query = $query->where("value_add", $request->value_add);
        }

        $is_valid_specification = $query->first();

        if ($is_valid_specification) {
            return response()->json([
                "message" => 403,
                "message_text" => "Ya existe una especificación con estos datos",
            ]);
        }

        $product_specification = ProductSpecification::create($request->all());

        return response()->json([
            "message" => 200,
            "specification" => [
                "id" => $product_specification->id,
                "product_id" => $product_specification->product_id,
                "attribute_id" => $product_specification->attribute_id,
                "attribute" => $product_specification->attribute ? [
                    "name" => $product_specification->attribute->name,
                    "type_attribute" => $product_specification->attribute->type_attribute,
                ] : null,
                "propertie_id" => $product_specification->propertie_id,
                "propertie" => $product_specification->propertie ? [
                    "name" => $product_specification->propertie->name,
                    "code" => $product_specification->propertie->code,
                ] : null,
                "value_add" => $product_specification->value_add,
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
        $query = ProductSpecification::where("product_id", $request->product_id)
            ->where("id", "<>", $id)
            ->where("attribute_id", $request->attribute_id);

        if ($request->propertie_id) {
            $query = $query->where("propertie_id", $request->propertie_id);
        } else {
            $query = $query->where("value_add", $request->value_add);
        }

        
        $is_valid_specification = $query->first();

        if ($is_valid_specification) {
            return response()->json([
                "message" => 403,
                "message_text" => "Ya existe una especificación con estos datos",
            ]);
        }

        $product_specification = ProductSpecification::findOrFail($id);
        $product_specification->update($request->all());

        return response()->json([
            "message" => 200,
            "specification" => [
                "id" => $product_specification->id,
                "product_id" => $product_specification->product_id,
                "attribute_id" => $product_specification->attribute_id,
                "attribute" => $product_specification->attribute ? [
                    "name" => $product_specification->attribute->name,
                    "type_attribute" => $product_specification->attribute->type_attribute,
                ] : null,
                "propertie_id" => $product_specification->propertie_id,
                "propertie" => $product_specification->propertie ? [
                    "name" => $product_specification->propertie->name,
                    "code" => $product_specification->propertie->code,
                ] : null,
                "value_add" => $product_specification->value_add,
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product_specification = ProductSpecification::findOrFail($id);
        $product_specification->delete();
        // Validacion para no eliminar en caso de que el producto o especificacion este en el carrito o detallado en una orden
        return response()->json([
            "message" => 200,
        ]);
    }
}
