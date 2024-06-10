<?php

namespace App\Models\Product;

use App\Models\Discount\DiscountProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "title",
        "slug",
        "sku",
        "price_pvp",
        "price_desc",
        "description",
        "summary",
        "image",
        "state",
        "tags",
        "brand_id",
        "stock",
        "categorie_first_id",
        "categorie_second_id",
        "categorie_third_id",
    ];

    public function setCreatedAtAttribute($value)
    {
        date_default_timezone_set(("America/Guayaquil"));
        $this->attributes['created_at'] = Carbon::now();
    }
    public function setUpdatedAtAttribute($value)
    {
        date_default_timezone_set(("America/Guayaquil"));
        $this->attributes['updated_at'] = Carbon::now();
    }
    public function categorie_first()
    {
        return $this->belongsTo(Categorie::class, "categorie_first_id");
    }
    public function categorie_second()
    {
        return $this->belongsTo(Categorie::class, "categorie_second_id");
    }
    public function categorie_third()
    {
        return $this->belongsTo(Categorie::class, "categorie_third_id");
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, "brand_id");
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class, "product_id");
    }
    public function discount_products()
    {
        return $this->hasMany(DiscountProduct::class, "product_id");
    }
    public function variations()
    {
        return $this->hasMany(ProductVariation::class, "product_id")->where("product_variation_id", null);
    }
    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class, "product_id");
    }
    public function getDiscountCategoryAttribute()
    {
        date_default_timezone_set(("America/Guayaquil"));
        $discount = null;
        foreach ($this->categorie_first->discount_categories as $discount_category) {
            if (
                $discount_category->discount
                && $discount_category->discount->type_campaign == 1
                && $discount_category->discount->state == 1
                && Carbon::now()->between($discount_category->discount->start_date, Carbon::parse($discount_category->discount->end_date)->addDay(1))
            ) {
                $discount = $discount_category->discount;
                break;
            }
        }
        return $discount;
    }
    public function getDiscountProductAttribute()
    {
        date_default_timezone_set(("America/Guayaquil"));
        $discount = null;
        foreach ($this->discount_products as $discount_product) {
            if (
                $discount_product->discount
                && $discount_product->discount->type_campaign == 1
                && $discount_product->discount->state == 1
                && Carbon::now()->between($discount_product->discount->start_date, Carbon::parse($discount_product->discount->end_date)->addDay(1))
            ) {
                $discount = $discount_product->discount;
                break;
            }
        }
        return $discount;
    }
    public function getDiscountBrandAttribute()
    {
        date_default_timezone_set(("America/Guayaquil"));
        $discount = null;
        foreach ($this->brand->discount_brands as $discount_brand) {
            if (
                $discount_brand->discount
                && $discount_brand->discount->type_campaign == 1
                && $discount_brand->discount->state == 1
                && Carbon::now()->between($discount_brand->discount->start_date, Carbon::parse($discount_brand->discount->end_date)->addDay(1))
            ) {
                $discount = $discount_brand->discount;
                break;
            }
        }
        return $discount;
    }

    public function scopeFilterAdvanceProduct($query, $search, $categorie_first_id, $categorie_second_id, $categorie_third_id, $brand_id)
    {
        if ($search) {
            $query->where("title", "like", "%" . $search . "%");
        }
        if ($categorie_first_id) {
            $query->where("categorie_first_id", $categorie_first_id);
        }
        if ($categorie_second_id) {
            $query->where("categorie_second_id", $categorie_second_id);
        }
        if ($categorie_third_id) {
            $query->where("categorie_third_id", $categorie_third_id);
        }
        if ($brand_id) {
            $query->where("brand_id", $brand_id);
        }

        return $query;
    }
}
