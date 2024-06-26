<?php

namespace App\Http\Resources\Ecommerce\Address;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            "user" => $this->resource->user ? [
                "id" => $this->resource->user->id,
                "name" => $this->resource->user->name. " " . $this->resource->user->last_name,
                "email" => $this->resource->user->email,
                "phone" => $this->resource->user->phone,
            ] : null,
            "province_id" => $this->resource->province_id,
            "province" => $this->resource->province ? [
                "id" => $this->resource->province->id,
                "name" => $this->resource->province->name,
            ] : null,
            "city_id" => $this->resource->city_id,
            "city" => $this->resource->city ? [
                "id" => $this->resource->city->id,
                "code" => $this->resource->city->code,
                "name" => $this->resource->city->name,
            ] : null,
            "parish_id" => $this->resource->parish_id,
            "parish" => $this->resource->parish ? [
                "id" => $this->resource->parish->id,
                "code" => $this->resource->parish->code,
                "name" => $this->resource->parish->name,
            ] : null,
            "company" => $this->resource->company,
            "main_street" => $this->resource->main_street,
            "secondary_street" => $this->resource->secondary_street,
            "reference" => $this->resource->reference,
            "sector" => $this->resource->sector,
            "house_number" => $this->resource->house_number,
            "created_at" => $this->resource->created_at->format("Y-m-d H:i:s"),
        ];
    }
}
