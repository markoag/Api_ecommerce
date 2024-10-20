<?php

namespace App\Http\Resources\Ecommerce\Sale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
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
            "user_id" => $this->resource->user_id,
            "user" => [
                "avatar" => $this->resource->user->avatar ? env("APP_URL") . "storage/" . $this->resource->user->avatar : 'https://cdn-icons-png.flaticon.com/512/1077/1077114.png',
                "full_name" => $this->resource->user->name . " " . $this->resource->user->last_name,
            ],
            "method_payment" => $this->resource->method_payment,
            "discount" => $this->resource->discount,
            "subtotal" => $this->resource->subtotal,
            "total" => $this->resource->total,
            "price" => $this->resource->price,
            "description" => $this->resource->description,
            "n_transaction" => $this->resource->n_transaction,
            "sale_details" => $this->resource->sale_details->map(function ($sale_detail) {
                $attribute = null;
                $propertie = null;
                $variation_father = null;

                if ($sale_detail->product_variation) {
                    if ($sale_detail->product_variation->attribute) {
                        $attribute = [
                            "name" => $sale_detail->product_variation->attribute->name,
                            "type_attribute" => $sale_detail->product_variation->attribute->type_attribute,
                        ];
                    }
                    if ($sale_detail->product_variation->propertie) {
                        $propertie = [
                            "name" => $sale_detail->product_variation->propertie->name,
                            "code" => $sale_detail->product_variation->propertie->code,
                        ];
                    }
                    if ($sale_detail->product_variation->variation_father) {
                        $variation_father = [
                            "id" => $sale_detail->product_variation->variation_father->id,
                            "attribute_id" => $sale_detail->product_variation->variation_father->attribute_id,
                            "attribute" => $sale_detail->product_variation->variation_father->attribute ? [
                                "name" => $sale_detail->product_variation->variation_father->attribute->name,
                                "type_attribute" => $sale_detail->product_variation->variation_father->attribute->type_attribute,
                            ] : null,
                            "propertie_id" => $sale_detail->product_variation->variation_father->propertie_id,
                            "propertie" => $sale_detail->product_variation->variation_father->propertie ? [
                                "name" => $sale_detail->product_variation->variation_father->propertie->name,
                                "code" => $sale_detail->product_variation->variation_father->propertie->code,
                            ] : null,
                            "value_add" => $sale_detail->product_variation->variation_father->value_add,
                        ];
                    }
                }
                return [
                    "id" => $sale_detail->id,
                    "product_id" => $sale_detail->product_id,
                    "product" => [
                        "id" => $sale_detail->product->id,
                        "title" => $sale_detail->product->title,
                        "slug" => $sale_detail->product->slug,
                        "price_pvp" => $sale_detail->product->price_pvp,
                        "price_desc" => $sale_detail->product->price_desc,
                        "image" => env("APP_URL") . "storage/" . $sale_detail->product->image,
                        "brand_id" => $sale_detail->product->brand_id,
                        "brand" => $sale_detail->product->brand ? [
                            "id" => $sale_detail->product->brand->id,
                            "name" => $sale_detail->product->brand->name
                        ] : null,
                    ],
                    "product_variation_id" => $sale_detail->product_variation_id,
                    "product_variation" => $sale_detail->product_variation ? [
                        "id" => $sale_detail->product_variation->id,
                        "attribute_id" => $sale_detail->product_variation->attribute_id,
                        "attribute" => $attribute,
                        "propertie_id" => $sale_detail->product_variation->propertie_id,
                        "propertie" => $propertie,
                        "value_add" => $sale_detail->product_variation->value_add,
                        "variation_father" => $variation_father,
                    ] : null,
                    "type_discount" => $sale_detail->type_discount,
                    "discount" => $sale_detail->discount,
                    "type_campaign" => $sale_detail->type_campaign,
                    "code_coupon" => $sale_detail->code_coupon,
                    "code_discount" => $sale_detail->code_discount,
                    "quantity" => $sale_detail->quantity,
                    "price_unit" => $sale_detail->price_unit,
                    "subtotal" => $sale_detail->subtotal,
                    "total" => $sale_detail->total,
                    "created_at" => $sale_detail->created_at->format("Y-m-d H:i A"),
                    "review" => $sale_detail->review,
                ];
            }),
            "sale_address_list" => $this->resource->sale_address_list->map(function ($sale_addres) {
                return [
                    "id" => $sale_addres->id,
                    "province_id" => $sale_addres->province_id,
                    "province" => $sale_addres->province ? [
                        "id" => $sale_addres->province->id,
                        "name" => $sale_addres->province->name,
                    ] : null,
                    "city_id" => $sale_addres->city_id,
                    "city" => $sale_addres->city ? [
                        "code" => $sale_addres->city->code,
                        "name" => $sale_addres->city->name,
                    ] : null,
                    "parish_id" => $sale_addres->parish_id,
                    "parish" => $sale_addres->parish ? [
                        "code" => $sale_addres->parish->code,
                        "name" => $sale_addres->parish->name,
                    ] : null,
                ];
            }),
            "created_at" => $this->resource->created_at->format("Y-m-d H:i A"),
            "state" => $this->resource->state,
        ];
    }
}
