<?php

namespace App\Models\Address;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parish extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "code",
        "city_id",
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

    // Relación de una parroquia con una ciudad
    public function city() {
        return $this->belongsTo(City::class, "code");
    }
}
