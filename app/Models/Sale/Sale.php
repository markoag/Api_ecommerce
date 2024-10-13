<?php

namespace App\Models\Sale;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "user_id",
        "method_payment",
        "discount",
        "subtotal",
        "total",
        "price",
        "description",
        "n_transaction",
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

    public function sale_details()
    {
        return $this->hasMany(SaleDetail::class);
    }
    public function sale_address()
    {
        return $this->hasOne(SaleAddres::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filterAdvancedAmin($search, $start_date, $end_date, $brand_id, $categorie_first_id, $categorie_second_id, $categorie_third_id, $method_payment)
    {
        $query = Sale::query();
        if ($search) {
            $query->whereHas("user", function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(users.name, ' ', IFNULL(users.last_name,''),' ', users.email"), "LIKE", "%" . $search . "%");
            });
        }

        if ($start_date && $end_date) {
            $query->whereBetween("created_at", [
                Carbon::parse($start_date)->format("Y-m-d") . " 00:00:00",
                Carbon::parse($end_date)->format("Y-m-d") . " 23:59:59"
            ]);
        }

        if ($brand_id) {
            $query->whereHas("sale_details", function ($q) use ($brand_id) {
                $q->whereHas("product", function ($sq) use ($brand_id) {
                    $sq->where("brand_id", $brand_id);
                });
            });
        }

        if ($categorie_first_id || $categorie_second_id || $categorie_third_id) {
            $query->whereHas("sale_details", function ($q) use ($categorie_first_id, $categorie_second_id, $categorie_third_id) {
                $q->whereHas("product", function ($sq) use ($categorie_first_id, $categorie_second_id, $categorie_third_id) {
                    if ($categorie_first_id) {
                        $sq->where("categorie_first_id", $categorie_first_id);
                    }
                    if ($categorie_second_id) {
                        $sq->where("categorie_second_id", $categorie_second_id);
                    }
                    if ($categorie_third_id) {
                        $sq->where("categorie_third_id", $categorie_third_id);
                    }
                });
            });
        }

        if ($method_payment) {
            $query->where("method_payment", $method_payment);
        }

        return $query;
    }
}
