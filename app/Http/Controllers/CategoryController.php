<?php

namespace App\Http\Controllers;

use App\Category;
use App\Helpers\Transformer;
use App\Http\Filters\CategoryFilter;
use App\Http\Resources\CategoriesCollection;
use App\Http\Resources\CategoryResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Category not found response.
     *
     * @return  JsonResponse
     */
    private function notFoundResponse()
    {
        return Transformer::fail('Category not found.', null, 404);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Category::query();
            $query = CategoryFilter::collection($request, $query);

            $limit = $request->get('limit', 15);
            $categories = $limit > 0 ? $query->paginate($limit) : $query->get();
            
            return (new CategoriesCollection($categories))
                        ->additional(
                            Transformer::meta(true, 'Success to get categories collection.')
                        );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get categories collection.');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, Category::validationRules());

        try {
            $category = Category::create([
                'name' => $request->get('name')
            ]);

            return Transformer::ok(
                'Success to create category.',
                [
                    'category' => new CategoryResource($category)
                ],
                201
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to create category.');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, Category::validationRules(true, $id));

        try {
            $category = Category::findOrFail($id);
            
            $category->update([
                'name' => $request->get('name')
            ]);

            return Transformer::ok(
                'Success to update category.',
                [
                    'category' => new CategoryResource($category)
                ]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update category.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $category = Category::select('id')->whereId($id)->firstOrFail();
            
            $category->delete();

            return Transformer::ok(
                'Success to delete category.'
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete category.');
        }
    }
}
