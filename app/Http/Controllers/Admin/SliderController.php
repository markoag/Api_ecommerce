<?php

namespace App\Http\Controllers\Admin;

use App\Models\Slider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $sliders = Slider::where("title", "LIKE", "%$search%")
            ->orderBy("id", "desc")
            ->paginate(10);

        return response()->json([
            "total" => $sliders->total(),
            "sliders" => $sliders->map(function ($slider) {
                return [
                    "id" => $slider->id,
                    "title" => $slider->title,
                    "label" => $slider->label,
                    "type" => $slider->type,
                    "type_view" => $slider->type_view,
                    "subtitle" => $slider->subtitle,
                    "link" => $slider->link,
                    "state" => $slider->state,
                    "color" => $slider->color,
                    "original_price" => $slider->original_price,
                    "campaign_price" => $slider->campaign_price,
                    "image" => env("APP_URL") . "storage/" . $slider->image,
                    "image2" => $slider->image2 ? env("APP_URL") . "storage/" . $slider->image2 : null,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("sliders", $request->file("imagen"));
            $request->request->add(["image" => $path]);
        }
        if ($request->hasFile("imagen2")) {
            $path = Storage::putFile("sliders", $request->file("imagen2"));
            $request->request->add(["image2" => $path]);
        }
        $slider = Slider::create($request->all());
        return response()->json(["message" => 200]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $slider = Slider::findOrFail($id);

        return response()->json(["slider" => [
            "id" => $slider->id,
            "title" => $slider->title,
            "label" => $slider->label,
            "type" => $slider->type,
            "type_view" => $slider->type_view,
            "subtitle" => $slider->subtitle,
            "link" => $slider->link,
            "state" => $slider->state,
            "color" => $slider->color,
            "original_price" => $slider->original_price,
            "campaign_price" => $slider->campaign_price,
            "image" => env("APP_URL") . "storage/" . $slider->image,
            "image2" => $slider->image2 ? env("APP_URL") . "storage/" . $slider->image2 : null,
        ]]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $slider = Slider::findOrFail($id);
        if ($request->hasFile("imagen")) {
            if ($slider->image) {
                Storage::delete($slider->image);
            }
            $path = Storage::putFile("sliders", $request->file("imagen"));
            $request->request->add(["image" => $path]);
        }
        if ($request->hasFile("imagen2")) {
            if ($slider->image2) {
                Storage::delete($slider->image2);
            }
            $path = Storage::putFile("sliders", $request->file("imagen2"));
            $request->request->add(["image2" => $path]);
        }
        $slider->update($request->all());
        return response()->json(["message" => 200]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $slider = Slider::findOrFail($id);
        $slider->delete();
        return response()->json(["message" => 200]);
    }
}
