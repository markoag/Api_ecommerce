<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\Cart\CartEcommerceCollection;
use App\Http\Resources\Ecommerce\Cart\CartEcommerceResource;
use App\Models\Coupon\Coupon;
use App\Models\Product\Product;
use App\Models\Product\ProductVariation;
use App\Models\Sale\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('api')->user();

        $carts = Cart::where("user_id", $user->id)->get();

        return response()->json([
            "carts" => CartEcommerceCollection::make($carts),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();

        $is_exists_cart = Cart::where("product_variation_id", $request->product_variation_id)
            ->where("product_id", $request->product_id)
            ->where("user_id", $user->id)
            ->first();

        if ($is_exists_cart) {
            $message_text = $request->product_variation_id
                ? "El producto con variación ya se encuentra en el carrito, actualice la cantidad si desea agregar más unidades"
                : "El producto ya se encuentra en el carrito, actualice la cantidad si desea agregar más unidades";

            return response()->json([
                "message" => 403,
                "message_text" => $message_text
            ]);
        } else {
            if ($request->product_variation_id) {
                // Validar stock del producto (variación)
                $variation = ProductVariation::find($request->product_variation_id);
                if ($variation && $variation->stock < $request->quantity) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "No hay suficiente stock para el producto con variación seleccionado"
                    ]);
                }
            } else {
                // Validar stock del producto
                $product = Product::find($request->product_id);
                if ($product && $product->stock < $request->quantity) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "No hay suficiente stock para el producto seleccionado"
                    ]);
                }
            }
        }

        error_log(json_encode($user));
        $request->request->add(["user_id" => $user->id]);
        $cart = Cart::create($request->all());

        return response()->json([
            "cart" => CartEcommerceResource::make($cart),
        ]);
    }

    public function apply_coupon(Request $request)
    {

        $coupon = Coupon::where("code", $request->code_coupon)->where("state", 1)->first();

        if (!$coupon) {
            return response()->json([
                "message" => 403,
                "message_text" => "El cupón ingresado no es válido"
            ]);
        }

        $user = auth('api')->user();

        $carts = Cart::where("user_id", $user->id)->get();

        foreach ($carts as $key => $cart) {
            if ($coupon->type_coupon == 1) { //NIVEL DE PRODUCTO
                $is_exists_product_coupon = false;
                foreach ($coupon->products as $coupon_product) {
                    if ($coupon_product->product_id == $cart->product_id) {
                        $is_exists_product_coupon = true;
                        break;
                    }
                }
                if ($is_exists_product_coupon) {
                    $subtotal = 0;
                    if ($coupon->type_discount == 1) { //PORCENTAJE
                        $subtotal = round($cart->price_unit - $cart->price_unit * ($coupon->discount / 100), 2);
                    } else {
                        $subtotal = round($cart->price_unit - $coupon->discount, 2);
                    }

                    // Validar si ya existe un descuento aplicado
                    // if (!$cart->code_discount) {
                    $cart->update([
                        "type_discount" => $coupon->type_discount,
                        "discount" => $coupon->discount,
                        "code_coupon" => $coupon->code,
                        "subtotal" => $subtotal,
                        "total" => $subtotal * $cart->quantity,
                        "type_campaign" => null,
                        "code_discount" => null,
                    ]);
                    // }
                }
            }
            if ($coupon->type_coupon == 2) { //NIVEL DE CATEGORIA
                $is_exists_category_coupon = false;
                foreach ($coupon->categories as $coupon_product) {
                    if ($coupon_product->categorie_id == $cart->product->categorie_first_id) {
                        $is_exists_category_coupon = true;
                        break;
                    }
                }
                if ($is_exists_category_coupon) {
                    $subtotal = 0;
                    if ($coupon->type_discount == 1) { //PORCENTAJE
                        $subtotal = round($cart->price_unit - $cart->price_unit * ($coupon->discount / 100), 2);
                    } else {
                        $subtotal = round($cart->price_unit - $coupon->discount, 2);
                    }

                    // Validar si ya existe un descuento aplicado
                    if (!$cart->code_discount) {
                        $cart->update([
                            "type_discount" => $coupon->type_discount,
                            "discount" => $coupon->discount,
                            "code_coupon" => $coupon->code,
                            "subtotal" => $subtotal,
                            "total" => $subtotal * $cart->quantity,
                            "type_campaign" => null,
                            "code_discount" => null,
                        ]);
                    }
                }
            }
            if ($coupon->type_coupon == 3) { //NIVEL DE MARCA
                $is_exists_brand_coupon = false;
                foreach ($coupon->brands as $coupon_product) {
                    if ($coupon_product->brand_id == $cart->product->brand_id) {
                        $is_exists_brand_coupon = true;
                        break;
                    }
                }
                if ($is_exists_brand_coupon) {
                    $subtotal = 0;
                    if ($coupon->type_discount == 1) { //PORCENTAJE
                        $subtotal = round($cart->price_unit - $cart->price_unit * ($coupon->discount / 100), 2);
                    } else {
                        $subtotal = round($cart->price_unit - $coupon->discount, 2);
                    }

                    // Validar si ya existe un descuento aplicado
                    // if (!$cart->code_discount) {
                    $cart->update([
                        "type_discount" => $coupon->type_discount,
                        "discount" => $coupon->discount,
                        "code_coupon" => $coupon->code,
                        "subtotal" => $subtotal,
                        "total" => $subtotal * $cart->quantity,
                        "type_campaign" => null,
                        "code_discount" => null,
                    ]);
                    // }
                }
            }
        }

        return response()->json([
            "message" => 200,
            "coupon" => $coupon,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth('api')->user();

        $is_exists_cart = Cart::where("product_variation_id", $request->product_variation_id)
            ->where("product_id", $request->product_id)
            ->where("id", "<>", $id)
            ->where("user_id", $user->id)
            ->first();

        if ($is_exists_cart) {
            $message_text = $request->product_variation_id
                ? "El producto con variación ya se encuentra en el carrito, actualice la cantidad si desea agregar más unidades"
                : "El producto ya se encuentra en el carrito, actualice la cantidad si desea agregar más unidades";

            return response()->json([
                "message" => 403,
                "message_text" => $message_text
            ]);
        } else {
            if ($request->product_variation_id) {
                // Validar stock del producto (variación)
                $variation = ProductVariation::find($request->product_variation_id);
                if ($variation && $variation->stock < $request->quantity) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "No hay suficiente stock para el producto con variación seleccionado"
                    ]);
                }
            } else {
                // Validar stock del producto
                $product = Product::find($request->product_id);
                if ($product && $product->stock < $request->quantity) {
                    return response()->json([
                        "message" => 403,
                        "message_text" => "No hay suficiente stock para el producto seleccionado"
                    ]);
                }
            }
        }

        $cart = Cart::findOrFail($id);
        $cart->update($request->all());

        return response()->json([
            "cart" => CartEcommerceResource::make($cart),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();

        return response()->json([
            "message" => 200
        ]);
    }
}
