<?php

namespace App\Models\Discount;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "code",
        "type_campaign",
        "type_discount",
        "discount",
        "discount_type",
        "state",
        "start_date",
        "end_date",
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
    public function categories()
    {
        return $this->hasMany(DiscountCategorie::class);
    }
    public function products()
    {
        return $this->hasMany(DiscountProduct::class);
    }
    public function brands()
    {
        return $this->hasMany(DiscountBrand::class);
    }
    public function scopeFilterAdvanceDiscount($query, $search, $start_date, $end_date)
    {
        if ($search) {
            $query->where("code", "LIKE", "%$search%");
        }
        // if ($type_campaign) {
        //     $query->where("type_campaign", $type_campaign);
        // }
        if ($start_date && $end_date) {
            $query->whereBetween("start_date", [$start_date, $end_date]);
        }
        return $query;
    }
}
