<?php

namespace App\Models\Address;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "code",
        "province_id",
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
    
    // Relación de una ciudad con una provincia
    public function province() {
        return $this->belongsTo(Province::class);
    }

    // Relación de una ciudad con muchas parroquias
    public function parishes() {
        return $this->hasMany(Parish::class, "city_id");
    }
}
