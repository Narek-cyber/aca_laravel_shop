<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Rate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $datas = $request->input('data');
        $order = Order::query()->findOrFail($request->input('order_id'));

        if ($order->user_id === auth()->id()) {
            foreach ($datas as $key => $data) {
                Rate::query()->create([
                    'user_id' => auth()->id(),
                    'order_id' => $request->input('order_id'),
                    'product_id' => $key,
                    'rate' => $data['rate'],
                    'comment' => $data['comment']
                ]);
            }
            $order->update(['rated' => 1]);

            return response()->json([
                'status' => 'successfully rated',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => "You don't have a permission",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $rate = Rate::query()->findOrFail($id);
        if ($rate->user_id == auth()->id()) {
            $rate->update([
                'rate' => $request->input('rate')
            ]);
            return response()->json([
                'message' => 'Successfully updated'
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => "You don't have a permission",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
        $rate = Rate::query()->findOrFail($id);
        if ($rate->user_id === auth()->id()) {
            $rate->delete();
            return response()->json([
                'message' => 'Rate successfully deleted'
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => "You don't have a permission",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
