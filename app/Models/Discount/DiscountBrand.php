<?php

namespace App\Models\Discount;

use App\Models\Product\Brand;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscountBrand extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "discount_id",
        "brand_id",
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
    public function brand() {
        return $this->belongsTo(Brand::class);
    }
}
