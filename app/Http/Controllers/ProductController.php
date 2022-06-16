<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /**
         * First Version
         */

        /**
         *  $products = Product::query()
                ->leftJoin('shops', 'products.shop_id', '=', 'shops.id')
                ->when($request->has('search'), function ($query) {
                    $query->where('products.name', 'like', "%" . request('search') . "%")
                    ->orWhere('shops.name', 'like', "%" . request('search') . "%");
                })->get();
         */

        $products = Product::query()
            ->select('products.*', 'shops.name as shop_name')
            ->leftJoin('shops', 'products.shop_id', '=', 'shops.id')
            ->when($request->has('search'), function ($query) {
                $query->where(function ($query) {
                    $query->where('products.name', 'like', "%" . request('search') . "%")
                        ->orWhere('products.description', 'like', "%" . request('search') . "%");
                })->orWhere('shops.name', 'like', "%" . request('search') . "%");
            })->paginate(10);

        $user = Auth::user();

        return response()->json([
            'message' => 'success',
//            'items' => Product::all(),
//            'items' => $user->products()->get(),
//            'items' => $user->products
            'items' => $products
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $inputs = $request->except('rating');
            $shop = Shop::query()->find($request->shop_id);

            if ($shop->user_id !== auth()->id()) {
                return response()->json([
                    "message" => "You don'\t have a permission create a product in this shop"
                ]);
            }

            $product = new Product();
            $product->fill($inputs);
            $product->save();

            return response()->json([
                'message' => 'successfully saved'
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = Product::query()->findOrFail($id);

            return response()->json([
                'message' => 'success',
                'products' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $inputs = $request->except('rating');
            $product = Product::query()->findOrFail($id);
            $product->fill($inputs);
            $product->update();

            return response()->json([
                'message' => 'successfully updated'
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            Product::query()->findOrFail($id)->delete();
            return response()->json(['message' => 'successfully deleted']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
