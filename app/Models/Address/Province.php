<?php

namespace App\Models\Address;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Province extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "name",
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

    // Relación de una provincia con muchas ciudades
    public function cities() {
        return $this->hasMany(City::class);
    }
}
