<?php

namespace App\Models\Product;

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
