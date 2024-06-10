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
        $discount_g = null;
        $discount_collect = collect([]);

        $discount_product = $this->resource->discount_product;
        if ($discount_product) {
            $discount_collect->push($discount_product);
        }
        $discount_category = $this->resource->discount_category;
        if ($discount_category) {
            $discount_collect->push($discount_category);
        }
        $discount_brand = $this->resource->discount_brand;
        if ($discount_brand) {
            $discount_collect->push($discount_brand);
        }

        if ($discount_collect->count() > 0) {
            $discount_g = $discount_collect->sortByDesc("discount")->first();
        }

        // Codigo mejorado para variaciones agrupadas y anidadas por funcionar
        // function getAttributeDetails($attribute) {
        //     return $attribute ? [
        //         "name" => $attribute->name,
        //         "type_attribute" => $attribute->type_attribute,
        //     ] : null;
        // }

        // function getPropertieDetails($propertie) {
        //     return $propertie ? [
        //         "name" => $propertie->name,
        //         "code" => $propertie->code,
        //     ] : null;
        // }

        // function getVariationDetails($variation) {
        //     return [
        //         "id" => $variation->id,
        //         "product_id" => $variation->product_id,
        //         "attribute_id" => $variation->attribute_id,
        //         "attribute" => getAttributeDetails($variation->attribute),
        //         "propertie_id" => $variation->propertie_id,
        //         "propertie" => getPropertieDetails($variation->propertie),
        //         "value_add" => $variation->value_add,
        //         "price_add" => $variation->price_add,
        //         "stock" => $variation->stock,
        //         "variation" => $variation->variation_children->count() > 0 ? getVariationDetails($variation->variation_children->first()) : null,
        //         "variations" => $variation->variation_children->map('getVariationDetails'),
        //     ];
        // }

        // $variation_collect = collect([]);
        // foreach ($this->resource->variations->groupBy("attribute_id") as $key => $variation_c) {
        //     $variation_collect->push([
        //         "attribute_id" => $variation_c[0]->attribute_id,
        //         "attribute" => getAttributeDetails($variation_c[0]->attribute),
        //         "details" => $variation_c->map('getVariationDetails'),
        //     ]);
        // }

        $variation_collect = collect([]);
        foreach ($this->resource->variations->groupBy("attribute_id") as $key => $variation_c) {
            $variation_collect->push([
                "attribute_id" => $variation_c[0]->attribute_id,
                "attribute" => $variation_c[0]->attribute ? [
                    "name" => $variation_c[0]->attribute->name,
                    "type_attribute" => $variation_c[0]->attribute->type_attribute,
                ] : null,
                "variations" => $variation_c->map(function ($variation) {
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
                        "subvariation" => $variation->variation_children->count() > 0 ? [
                            "attribute_id" => $variation->variation_children->first()->attribute_id,
                            "attribute" => $variation->variation_children->first()->attribute ? [
                                "name" => $variation->variation_children->first()->attribute->name,
                                "type_attribute" => $variation->variation_children->first()->attribute->type_attribute,
                            ] : null,
                        ] : null,
                        "subvariations" => $variation->variation_children->map(function ($subvariation) {
                            return [
                                "id" => $subvariation->id,
                                "product_id" => $subvariation->product_id,
                                "attribute_id" => $subvariation->attribute_id,
                                "attribute" => $subvariation->attribute ? [
                                    "name" => $subvariation->attribute->name,
                                    "type_attribute" => $subvariation->attribute->type_attribute,
                                ] : null,
                                "propertie_id" => $subvariation->propertie_id,
                                "propertie" => $subvariation->propertie ? [
                                    "name" => $subvariation->propertie->name,
                                    "code" => $subvariation->propertie->code,
                                ] : null,
                                "value_add" => $subvariation->value_add,
                                "price_add" => $subvariation->price_add,
                                "stock" => $subvariation->stock,
                            ];
                        }),
                    ];
                }),
            ]);
        }

        $tags_parse = [];
        foreach (($this->resource->tags ? json_decode($this->resource->tags, true) : []) as $key => $tag) {
            array_push($tags_parse, $tag["item_text"]);
        }
        $tags_parse = implode(', ', $tags_parse);

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
            "tags_parse" => $tags_parse,
            "brand_id" => $this->resource->brand_id,
            "brand" => $this->resource->brand ? [
                "id" => $this->resource->brand->id,
                "name" => $this->resource->brand->name
            ] : null,
            "stock" => $this->resource->stock,
            "categorie_first_id" => $this->resource->categorie_first_id,
            "categorie_first" => $this->resource->categorie_first ? [
                "id" => $this->resource->categorie_first->id,
                "name" => $this->resource->categorie_first->name
            ] : null,
            "categorie_second_id" => $this->resource->categorie_second_id,
            "categorie_second" => $this->resource->categorie_second ? [
                "id" => $this->resource->categorie_second->id,
                "name" => $this->resource->categorie_second->name
            ] : null,
            "categorie_third_id" => $this->resource->categorie_third_id,
            "categorie_third" => $this->resource->categorie_third ? [
                "id" => $this->resource->categorie_third->id,
                "name" => $this->resource->categorie_third->name
            ] : null,
            "created_at" => $this->resource->created_at->format("Y-m-d H:i:s"),
            "images" => $this->resource->images->map(function ($image) {
                return [
                    "id" => $image->id,
                    "image" => env("APP_URL") . "storage/" . $image->image,
                ];
            }),
            "discount_collect" => $discount_collect,
            "discount_g" => $discount_g,
            "variations" => $variation_collect,
            "specifications" => $this->resource->specifications->map(function ($specification) {
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
        ];
    }
}
