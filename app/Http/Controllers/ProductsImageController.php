<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use \Illuminate\Http\JsonResponse;

class ProductsImageController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function store(Request $request, $id): JsonResponse
    {
        $user = auth()->user();

        try {
            if ($user->products()->where('products.id', $id)->exists()) {
                $folder = date('Y-m');

                if ($request->hasFile('logo')) {
                    $image = $request->file('logo');
                    $image_name = time() . '.' . $image->getClientOriginalName();
                    $image->move(public_path("media/images/{$folder}"), $image_name);
                    $image_file = "media/images/{$folder}/" . $image_name;
                }

                ProductImage::query()->create([
                    'path' => $image_file ?? null,
                    'name' => $request->input('name'),
                    'product_id' => $id,
                    'order' => $request->input('order')
                ]);

                return response()->json([
                    'message' => 'successfully saved',
                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'message' => 'you dont have a permission',
                ], Response::HTTP_METHOD_NOT_ALLOWED);
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
            $productImage = ProductImage::query()->findOrFail($id);

            $path = $productImage->path;
            $file_old = public_path() . "/$path";

            if ($path && file_exists($file_old)) {
                unlink($file_old);
            }

            $productImage->delete();

            return response()->json([
                'message' => 'successfully deleted',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function reorder(Request $request, $id): JsonResponse
    {
        $product = Product::query()->find($id);
        $productImages = $product->images;
        $product_images_orders = $request->input('images');

        foreach ($productImages as $image) {
            $image->update([
                'order' => $product_images_orders[$image->id]
            ]);
        }

        return response()->json([
            'message' => 'successfully ordered',
        ], Response::HTTP_OK);
    }
}


