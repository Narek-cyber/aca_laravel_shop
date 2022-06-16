<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = Cart::query()
            ->where('user_id', auth()->id())
            ->with('product:id,shop_id,category_id,name,description')
            ->get();

        return response()->json([
            'message' => 'success',
            'carts' => $data
        ], Response::HTTP_OK);
    }

    /**
     * @param StoreCartRequest $request
     * @return JsonResponse
     */
    public function store(StoreCartRequest $request): JsonResponse
    {
        try {
            $cart = $request->validated();
            $cart['user_id'] = auth()->user()->{'id'};

            Cart::query()->create($cart);

            return response()->json([
                'message' => 'successfully saved',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateCartRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(UpdateCartRequest $request, $id): JsonResponse
    {
        $cart = Cart::query()->findOrFail($id);
        $product = $cart->product()->where('id', $request->product_id)->get();

        try {
            if ($cart->user_id == auth()->user()->id && $product[0]->count > $request->input('count')) {

                Cart::query()->create([
                    'count' => $request->input('count'),
                    'user_id' => auth()->id(),
                    'product_id' => $request->input('product_id')
                ]);

                return response()->json([
                    'message' => 'successfully updated',
                ], Response::HTTP_OK);

            } else {
                return response()->json([
                    'message' => 'You dont have permission',
                ], Response::HTTP_NOT_FOUND);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $cart = Cart::query()->findOrFail($id);

            if ($cart->{'user_id'} == auth()->id()) {
                $cart->delete();

                return response()->json([
                    'message' => 'successfully deleted'
                ]);
            } else {
                return response()->json([
                    'message' => 'You dont have permission'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return JsonResponse
     */
    public function checkout(): JsonResponse
    {
        try {
            $sum = 0;
            $cartProducts = Cart::query()
                ->where('user_id', auth()->id())
                ->with('product')
                ->get();

            if (count($cartProducts) > 0) {
                $productIds = [];
                $products = [];

                foreach ($cartProducts as $item) {
                    $sum += $item->product->count * $item->product->price;
                    $productIds[] = $item->product->id;

                    if ($item->product->count > $item->count) {
                        $item->product->update([
                            'count' => $item->product->count - $item->count
                        ]);
                    }

                    $products[] = [
                        'product_id' => $item->product->id,
                        'shop_id' => $item->product->shop_id,
                        'category_id' => $item->product->category_id,
                        'product_name' => $item->product->name,
                        'price' => $item->product->price
                    ];
                }

                Cart::query()->whereIn('product_id', $productIds)->delete();

                Order::query()->create([
                    'user_id' => auth()->id(),
                    'products' => json_encode($products),
                    'sum' => $sum,
                    'status' => 0
                ]);

                return response()->json([
                    'message' => 'Success shopping'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'You dont have permission'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
