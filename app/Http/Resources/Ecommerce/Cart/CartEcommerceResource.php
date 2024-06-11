<?php

namespace App\Http\Resources\Ecommerce\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartEcommerceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $attribute = null;
        $propertie = null;
        $variation_father = null;

        if ($this->resource->product_variation) {
            if ($this->resource->product_variation->attribute) {
                $attribute = [
                    "name" => $this->resource->product_variation->attribute->name,
                    "type_attribute" => $this->resource->product_variation->attribute->type_attribute,
                ];
            }
            if ($this->resource->product_variation->propertie) {
                $propertie = [
                    "name" => $this->resource->product_variation->propertie->name,
                    "code" => $this->resource->product_variation->propertie->code,
                ];
            }
            if ($this->resource->product_variation->variation_father) {
                $variation_father = [
                    "id" => $this->resource->product_variation->variation_father->id,
                    "attribute_id" => $this->resource->product_variation->variation_father->attribute_id,
                    "attribute" => $this->resource->product_variation->variation_father->attribute ? [
                        "name" => $this->resource->product_variation->variation_father->attribute->name,
                        "type_attribute" => $this->resource->product_variation->variation_father->attribute->type_attribute,
                    ] : null,
                    "propertie_id" => $this->resource->product_variation->variation_father->propertie_id,
                    "propertie" => $this->resource->product_variation->variation_father->propertie ? [
                        "name" => $this->resource->product_variation->variation_father->propertie->name,
                        "code" => $this->resource->product_variation->variation_father->propertie->code,
                    ] : null,
                    "value_add" => $this->resource->product_variation->variation_father->value_add,
                ];
            }
        }
        // if ($this->resource->product_variation->attribute) {
        //     $attribute = [
        //         "name" => $this->resource->product_variation->attribute->name,
        //         "type_attribute" => $this->resource->product_variation->attribute->type_attribute,
        //     ];
        // }
        
        // if ($this->resource->product_variation->propertie) {
        //     $propertie = [
        //         "name" => $this->resource->product_variation->propertie->name,
        //         "code" => $this->resource->product_variation->propertie->code,
        //     ];
        // }
        
        // if ($this->resource->product_variation->variation_father) {
        //     $variation_father = [
        //         "id" => $this->resource->product_variation->variation_father->id,
        //         "attribute_id" => $this->resource->product_variation->variation_father->attribute_id,
        //         "attribute" => $this->resource->product_variation->variation_father->attribute ? [
        //             "name" => $this->resource->product_variation->variation_father->attribute->name,
        //             "type_attribute" => $this->resource->product_variation->variation_father->attribute->type_attribute,
        //         ] : null,
        //         "propertie_id" => $this->resource->product_variation->variation_father->propertie_id,
        //         "propertie" => $this->resource->product_variation->variation_father->propertie ? [
        //             "name" => $this->resource->product_variation->variation_father->propertie->name,
        //             "code" => $this->resource->product_variation->variation_father->propertie->code,
        //         ] : null,
        //         "value_add" => $this->resource->product_variation->variation_father->value_add,
        //     ];
        // }

        return [
            "id" => $this->resource->id,
            "user_id" => $this->resource->user_id,
            "product_id" => $this->resource->product_id,
            "product" => [
                "id" => $this->resource->product->id,
                "title" => $this->resource->product->title,
                "slug" => $this->resource->product->slug,
                "price_pvp" => $this->resource->product->price_pvp,
                "price_desc" => $this->resource->product->price_desc,
                "image" => env("APP_URL") . "storage/" . $this->resource->product->image,
                "brand_id" => $this->resource->product->brand_id,
                "brand" => $this->resource->product->brand ? [
                    "id" => $this->resource->product->brand->id,
                    "name" => $this->resource->product->brand->name
                ] : null,
            ],
            "product_variation_id" => $this->resource->product_variation_id,
            "product_variation" => $this->resource->product_variation ? [
                "id" => $this->resource->product_variation->id,
                "attribute_id" => $this->resource->product_variation->attribute_id,
                "attribute" => $attribute,
                "propertie_id" => $this->resource->product_variation->propertie_id,
                "propertie" => $propertie,
                "value_add" => $this->resource->product_variation->value_add,
                "variation_father" => $variation_father,
            ] : null,
            "type_discount" => $this->resource->type_discount,
            "discount" => $this->resource->discount,
            "type_campaign" => $this->resource->type_campaign,
            "code_coupon" => $this->resource->code_coupon,
            "code_discount" => $this->resource->code_discount,
            "quantity" => $this->resource->quantity,
            "price_unit" => $this->resource->price_unit,
            "subtotal" => $this->resource->subtotal,
            "total" => $this->resource->total,
            "created_at" => $this->resource->created_at->format("Y-m-d H:i:s"),
        ];
    }
}
