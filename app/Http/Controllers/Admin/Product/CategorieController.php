<?php

namespace App\Http\Controllers\Admin\Product;

use Illuminate\Http\Request;
use App\Models\Product\Categorie;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Product\CategorieResource;
use App\Http\Resources\Product\CategorieCollection;

class CategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $categories = Categorie::where("name", "LIKE", "%$search%")
            ->orderBy("position", "ASC")
            ->orderBy("type_categorie", "ASC")
            ->orderBy("categorie_second_id", "ASC")
            ->orderBy("categorie_third_id", "ASC")
            ->orderBy("name", "ASC")
            ->paginate(10);

        return response()->json([
            "total" => $categories->total(),
            "categories" => CategorieCollection::make($categories),
        ]);
    }

    public function config()
    {
        $categories_first = Categorie::where("categorie_second_id", null)
            ->where("categorie_third_id", null)
            ->get();
        $categories_seconds = Categorie::where("categorie_second_id", "<>", null)
            ->where("categorie_third_id", null)
            ->get();

        return response()->json([
            "categories_first" => $categories_first,
            "categories_seconds" => $categories_seconds,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $is_exists = Categorie::where("name", $request->name)->first();
        if ($is_exists) {
            return response()->json(["message" => 403]);
        }
        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("categories", $request->file("imagen"));
            $request->request->add(["image" => $path]);
        }
        $categorie = Categorie::create($request->all());
        return response()->json(["message" => 200]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $categorie = Categorie::findOrFail($id);

        return response()->json(["categorie" => CategorieResource::make($categorie)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $is_exists = Categorie::where("id", '<>', $id)->where("name", $request->name)->first();
        if ($is_exists) {
            return response()->json(["message" => 403]);
        }
        $categorie = Categorie::findOrFail($id);
        if ($request->hasFile("imagen")) {
            if ($categorie->image) {
                Storage::delete($categorie->image);
            }
            $path = Storage::putFile("categories", $request->file("imagen"));
            $request->request->add(["image" => $path]);
        }
        $categorie->update($request->all());
        return response()->json(["message" => 200]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $categorie = Categorie::findOrFail($id);
        // Validar que la categoría no tenga productos asociados
        if (
            $categorie->product_categorie_first->count() > 0 ||
            $categorie->product_categorie_second->count() > 0 ||
            $categorie->product_categorie_third->count() > 0
        ) {
            return response()->json([
                "message" => 403,
                "message_text" => "La categoría no puede ser eliminada porque tiene productos asociados",
            ]);
        }

        $categorie->delete();

        return response()->json(["message" => 200]);
    }
}
