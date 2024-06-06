<?php

namespace App\Models\Discount;

use App\Models\Product\Categorie;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscountCategorie extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "discount_id",
        "categorie_id",
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
    public function categorie() {
        return $this->belongsTo(Categorie::class);
    }
    public function discount() {
        return $this->belongsTo(Discount::class);
    }
}
