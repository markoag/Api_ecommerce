<?php

namespace App\Models\Sale;

use App\Models\Product\Product;
use App\Models\Product\ProductVariation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_variation_id',
        'type_discount',
        'discount',
        'type_campaign',
        'code_coupon',
        'code_discount',
        'quantity',
        'price_unit',
        'subtotal',
        'total',
        'updated_at',
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
    public function sale() {
        return $this->belongsTo(Sale::class);
    }
    public function product() {
        return $this->belongsTo(Product::class);
    }
    public function product_variation() {
        return $this->belongsTo(ProductVariation::class);
    }
}
