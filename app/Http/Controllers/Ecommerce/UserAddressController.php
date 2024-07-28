<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\Address\AddressCollection;
use App\Http\Resources\Ecommerce\Address\AddressResource;
use App\Models\Address\City;
use App\Models\Address\Parish;
use App\Models\Address\Province;
use App\Models\Sale\UserAddres;
use App\Models\User;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('api')->user();
        $address = UserAddres::where("user_id", $user->id)->orderBy("id", "desc")->get();

        return response()->json([
            // "address" => $address
            "address" => AddressCollection::make($address)
        ]);
    }

    public function config()
    {
        // Listar todas las provincias del país
        $provinces = Province::all();
        $provinces = $provinces->map(function ($province) {
            return [
                "id" => $province->id,
                "name" => $province->name
            ];
        });
        // Listar todas las ciudades del país
        $cities = City::all();
        $cities = $cities->map(function ($city) {
            return [
                "province_id" => $city->province_id,
                "code" => $city->code,
                "name" => $city->name
            ];
        });
        // Listar todas las parroquias del país
        $parishes = Parish::all();
        $parishes = $parishes->map(function ($parish) {
            return [
                "city_id" => $parish->city_id,
                "code" => $parish->code,
                "name" => $parish->name
            ];
        });

        return response()->json([
            "provinces" => $provinces,
            "cities" => $cities,
            "parishes" => $parishes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->request->add(["user_id" => auth('api')->user()->id]);
        $addres = UserAddres::create($request->all());

        return response()->json([
            "addres" => $addres
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
        $addres = UserAddres::findOrFail($id);
        $addres->update($request->all());

        return response()->json([
            "addres" => AddressResource::make($addres)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $addres = UserAddres::findOrFail($id);
        $addres->delete();

        return response()->json([
            "message" => 200
        ]);
    }
}
