<?php

namespace App\Http\Resources\Ecommerce\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductEcommerceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "title" => $this->resource->title,
            "slug" => $this->resource->slug,
            "sku" => $this->resource->sku,
            "price_pvp" => $this->resource->price_pvp,
            "price_desc" => $this->resource->price_desc,
            "description" => $this->resource->description,
            "summary" => $this->resource->summary,
            "image" => env("APP_URL") . "storage/" . $this->resource->image,
            "state" => $this->resource->state,
            "tags" => $this->resource->tags ? json_decode($this->resource->tags) : [],
            "brand_id" => $this->resource->brand_id,
            "brand" => $this->resource->brand ? [
                "id" => $this->resource->brand->id,
                "name" => $this->resource->brand->name
            ] : null,
            "stock" => $this->resource->stock,
            "categorie_first_id" => $this->resource->categorie_first_id,
            "categorie_first" => $this->resource->categorie_first ? [
                "id"=> $this->resource->categorie_first->id,
                "name"=> $this->resource->categorie_first->name
            ] : null,
            "categorie_second_id" => $this->resource->categorie_second_id,
            "categorie_second" => $this->resource->categorie_second ? [
                "id"=> $this->resource->categorie_second->id,
                "name"=> $this->resource->categorie_second->name
            ] : null,
            "categorie_third_id" => $this->resource->categorie_third_id,
            "categorie_third" => $this->resource->categorie_third ? [
                "id"=> $this->resource->categorie_third->id,
                "name"=> $this->resource->categorie_third->name
            ] : null,
            "created_at" => $this->resource->created_at->format("Y-m-d H:i:s"),
            "images" => $this->resource->images->map(function ($image) {
                return [
                    "id" => $image->id,
                    "image" => env("APP_URL") . "storage/" . $image->image,
                ];
            }),
        ];
    }
}
