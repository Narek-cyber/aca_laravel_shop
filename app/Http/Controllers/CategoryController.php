<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->select('categories.*', 'categories.name as cname')
            ->leftJoin('categories as c', 'categories.parent_id', '=', 'c.id')
            ->when($request->has('search'), function ($query) {
                $query->where('categories.name', 'like', "%" . request('search') . "%")
                    ->orWhere('c.name', 'like', "%" . request('search') . "%");
            })->paginate(10);

        return response()->json([
            'message' => 'success',
            'items' => $categories
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = new Category();
            $category->fill($request->validated());
            $category->save();

            return response()->json([
                'message' => 'successfully saved',
                'date' => $category
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $products = Category::query()
                ->select('categories.name as category_name',
                    'categories.id as category_id',
                    'categories.parent_id as category_parent_id',
                    'products.*')
                ->leftJoin('products', 'categories.id', '=', 'products.category_id')
                ->where('categories.id', '=', $id)
                ->get();

            return response()->json([
                'message' => 'success',
                'products' => $products
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
     * @param UpdateCategoryRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(UpdateCategoryRequest $request, $id): JsonResponse
    {
        try {
            $category = Category::query()->findOrFail($id);
            $category->fill($request->validated());
            $category->update();

            return response()->json([
                'message' => 'successfully updated'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            Category::query()->findOrFail($id)->delete();
            return response()->json(['message' => 'successfully deleted']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
