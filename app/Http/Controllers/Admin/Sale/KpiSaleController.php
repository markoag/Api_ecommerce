<?php

namespace App\Http\Controllers\Admin\Sale;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Round;

class KpiSaleController extends Controller
{
    public function kpi_sales_province_year(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $query = DB::table('sales')
            ->where("sales.deleted_at", NULL)
            ->join("sale_addres", "sale_addres.sale_id", "=", "sales.id")
            ->join("provinces", "provinces.id", "=", "sale_addres.province_id")
            ->whereYear('sales.created_at', $year);
        if ($month != null) {
            $query->whereMonth('sales.created_at', $month);
        }
        $query->select("provinces.name as province", DB::raw('count(sales.id) as total_sales'), DB::raw('ROUND(sum(sales.total),2) as total_amount'))
            ->groupBy("provinces.id")
            ->orderBy("total_amount", "desc");
        $query = $query->get();
        return response()->json([
            "sales_for_province" => $query,
        ]);
    }

    public function kpi_sales_week_categories()
    {
        // Semana actual
        $start_week = Carbon::now()->startOfWeek()->format('Y-m-d') . " 00:00:00";
        $end_week = Carbon::now()->endOfWeek()->format('Y-m-d') . " 23:59:59";

        $sales_week = DB::table("sales")->where("sales.deleted_at", NULL)
            ->whereBetween("sales.created_at", [$start_week, $end_week])
            ->sum("sales.total");

        // Semana anterior
        $start_week_last = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d') . " 00:00:00";
        $end_week_last = Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d') . " 23:59:59";

        $sales_week_last = DB::table("sales")->where("sales.deleted_at", NULL)
            ->whereBetween("sales.created_at", [$start_week_last, $end_week_last])
            ->sum("sales.total");

        // Porcentaje de incremento o decremento
        $percentage = 0;
        if ($sales_week_last > 0) {
            $percentage = round((($sales_week - $sales_week_last) / $sales_week_last) * 100, 2);
        }

        // 3 categorías más vendidas en la semana actual
        $sales_weeek_categories = DB::table("sales")
            ->join("sale_details", "sale_details.sale_id", "=", "sales.id")
            ->join("products", "products.id", "=", "sale_details.product_id")
            ->join("categories", "categories.id", "=", "products.categorie_first_id")
            ->where("sales.deleted_at", NULL)
            ->whereBetween("sales.created_at", [$start_week, $end_week])
            ->select("categories.name as category", DB::raw('ROUND(SUM(sales.total),2) as total_sales'))
            ->groupBy("categories.id")
            ->orderBy("total_sales", "desc")
            ->limit(3)
            ->get();

        return response()->json([
            "sales_week" => round($sales_week, 2),
            "percentage" => $percentage,
            "sales_week_categories" => $sales_weeek_categories,
        ]);
    }

    public function kpi_sales_week_discounts()
    {
        $start_week = Carbon::now()->startOfWeek()->format('Y-m-d') . " 00:00:00";
        $end_week = Carbon::now()->endOfWeek()->format('Y-m-d') . " 23:59:59";

        $sales_week_discounts = DB::table("sales")
            ->join("sale_details", "sale_details.sale_id", "=", "sales.id")
            ->where("sale_details.deleted_at", NULL)
            ->where("sales.deleted_at", NULL)
            ->whereBetween("sales.created_at", [$start_week, $end_week])
            ->sum("sale_details.discount");

        $start_week_last = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d') . " 00:00:00";
        $end_week_last = Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d') . " 23:59:59";

        $sales_week_discounts_last = DB::table("sales")
            ->join("sale_details", "sale_details.sale_id", "=", "sales.id")
            ->where("sale_details.deleted_at", NULL)
            ->where("sales.deleted_at", NULL)
            ->whereBetween("sales.created_at", [$start_week_last, $end_week_last])
            ->sum("sale_details.discount");

        $percentage = 0;
        if ($sales_week_discounts_last > 0) {
            $percentage = round((($sales_week_discounts - $sales_week_discounts_last) / $sales_week_discounts_last) * 100, 2);
        }

        // Descuentos por día
        $sales_week_discounts_for_day = DB::table("sales")
            ->join("sale_details", "sale_details.sale_id", "=", "sales.id")
            ->where("sale_details.deleted_at", NULL)
            ->where("sales.deleted_at", NULL)
            ->whereBetween("sales.created_at", [$start_week, $end_week])
            ->select(DB::raw("DATE_FORMAT(sales.created_at, '%Y-%m-%d') as day"), DB::raw('ROUND(SUM(sale_details.discount),2) as total_discount'))
            ->groupBy("day")
            ->orderBy("day")
            ->get();

        // Total de descuentos y porcentaje por día
        $discount_for_days = collect([]);
        foreach ($sales_week_discounts_for_day as $key => $sales_week_discount) {
            $discount_for_days->push([
                "date" => $sales_week_discount->day,
                "total_discount_day" => round($sales_week_discount->total_discount, 2),
                "porcentage" => round((($sales_week_discount->total_discount) / $sales_week_discounts) * 100, 2),
            ]);
        }

        return response()->json([
            "sales_week_discounts" => round($sales_week_discounts, 2),
            "discount_for_days" => $discount_for_days,
            "percentage" => $percentage,
        ]);
    }

    public function kpi_sales_month_selected(Request $request)
    {
        $year = $request->year;
        $month = $request->month;

        $sales_for_day_of_month = DB::table('sales')
            ->where("sales.deleted_at", NULL)
            ->whereYear('sales.created_at', $year)
            ->whereMonth('sales.created_at', $month)
            ->select(
                DB::raw("DATE_FORMAT(sales.created_at, '%Y-%m-%d') as date"),
                DB::raw("DATE_FORMAT(sales.created_at, '%m-%d') as day"),
                DB::raw('ROUND(sum(sales.total),2) as total_sales'),
            )
            ->groupBy("date", "day")
            ->orderBy("date")
            ->get();

        $month_last = Carbon::parse($year . '-' . $month . '-' . '01')->subMonth();
        $sales_for_month_last = DB::table('sales')
            ->where("sales.deleted_at", NULL)
            ->whereYear('sales.created_at', $month_last->year)
            ->whereMonth('sales.created_at', $month_last->month)
            ->select(DB::raw('ROUND(sum(sales.total),2) as total_sales'))
            ->first();

        $porcentage = 0;
        if ($sales_for_month_last->total_sales > 0) {
            $porcentage = round((($sales_for_day_of_month->sum("total_sales") - $sales_for_month_last->total_sales) / $sales_for_month_last->total_sales) * 100, 2);
        }

        return response()->json([
            "sales_for_month_last" => $sales_for_month_last,
            "porcentage" => $porcentage,
            "sales_for_day_month" => $sales_for_day_of_month,
            "total_sales_month" => round($sales_for_day_of_month->sum("total_sales"), 2),
        ]);
    }
}
