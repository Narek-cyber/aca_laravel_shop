<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */

        /**
         *         $user = auth()->user();
         *
         * $shops = Shop::query()
         * ->when($user->isSeller(), function ($query) use ($user) {
         * $query->where('user_id', $user->id);
         * });
         */

        $shops = Shop::query()
            ->select('shops.*')
            ->when($request->has('search'), function ($query) {
                $query->where('shops.name', 'like', "%" . request('search') . "%");
            })->paginate(10);

        return response()->json([
            'status' => trans('shop.success'),
            'data' => $shops
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreStoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreStoreRequest $request): JsonResponse
    {
        try {
            $inputs = $request->except('rating', 'user_id');
            $inputs['user_id'] = auth()->id();
            $shop = new Shop();
            $shop->fill($inputs);
            $shop->save();
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
        /**
         * First Version
         */

        /**
         * try {
                $shop = Shop::query()->findOrFail($id);
                    $products = Shop::query()
                    ->join('products', 'shops.id', '=', 'products.shop_id')
                    ->where('shops.id', '=', $id)
                    ->get();

                    return response()->json([
                        'message' => 'success',
                        'shop' => $shop,
                        'products' => $products
                    ]);
                    } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    return response()->json([
                    'message' => $e->getMessage()
                ], 500);
            }
         */

        /** @var User $user */
        $user = auth()->user();
        $shop = Shop::query()
            ->when($user->isSeller(), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($id);

        return response()->json([
            'data' => $shop
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateStoreRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateStoreRequest $request, int $id): JsonResponse
    {
        try {
            $inputs = $request->except('rating');
            $shop = Shop::query()->findOrFail($id);
            $shop->fill($inputs);
            $shop->update();

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
            Shop::query()->findOrFail($id)->delete();
            return response()->json(['message' => 'successfully deleted']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
