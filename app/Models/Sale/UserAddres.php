<?php

namespace App\Models\Sale;

use App\Models\Address\City;
use App\Models\Address\Parish;
use App\Models\Address\Province;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddres extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "user_id",
        "province_id",
        "city_id",
        "parish_id",
        "company",
        "main_street",
        "secondary_street",
        "reference",
        "sector",
        "house_number",
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

    // Relación de un usuario con muchas direcciones
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    // Relación de un usuario con muchas provincias
    public function province()
    {
        return $this->belongsTo(Province::class, "province_id");
    }

    // Relación de un usuario con muchas ciudades
    public function city()
    {
        return $this->belongsTo(City::class, "city_id");
    }

    // Relación de un usuario con muchas parroquias
    public function parish()
    {
        return $this->belongsTo(Parish::class, "parish_id");
    }
}
