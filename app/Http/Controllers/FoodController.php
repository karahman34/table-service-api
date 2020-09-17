<?php

namespace App\Http\Controllers;

use App\Food;
use App\Helpers\Transformer;
use App\Http\Filters\FoodFilter;
use App\Http\Resources\FoodResource;
use App\Http\Resources\FoodsCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class FoodController extends Controller
{
    /**
     * Food image path.
     *
     * @var string
     */
    private $image_path;

    public function __construct()
    {
        $this->image_path = base_path('public/' . Food::$image_folder);
    }

    /**
     * Json not found response.
     *
     * @return  JsonResponse
     */
    private function notFoundResponse()
    {
        return Transformer::fail('Food not found.', null, 404);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Food::query();
            $query = FoodFilter::collection($request, $query);

            $limit = $request->get('limit', 15);
            $foods = (int) $limit > 0 ? $query->paginate($limit) : $query->get();

            return (new FoodsCollection($foods))
                        ->additional(
                            Transformer::meta(true, 'Success to get foods collection.')
                        );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get foods collection.');
        }
    }

    /**
     * Store uploaded image file into storage.
     *
     * @param   UploadedFile  $image_file
     *
     * @return  string
     */
    private function storeImage(UploadedFile $image_file)
    {
        $destination = $this->image_path;
        $ext = $image_file->getClientOriginalExtension();
        $final_file_name = time() . '.' . $ext;

        $image_file->move($destination, $final_file_name);

        return Food::$image_folder . '/' . $final_file_name;
    }

    /**
     * Delete current image food.
     *
     * @param   string  $current_image_path
     *
     * @return  bool|Exception
     */
    private function deleteImage(string $current_image_path)
    {
        $current_image_path = base_path('public/')  . $current_image_path;
        if (file_exists($current_image_path)) {
            return unlink($current_image_path);
        }

        return new \Exception('Invalid image path.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, Food::validationRules());

        try {
            $payload = $request->only(array_keys(Food::validationRules()));

            if ($request->hasFile('image')) {
                $image_file = $request->file('image');
                $payload['image'] = $this->storeImage($image_file);
            }

            $food = Food::create($payload);

            return Transformer::ok(
                'Success to save the food data.',
                [
                    'food' => new FoodResource($food)
                ],
                201
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to save the food data.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $food =Food::findOrFail($id);

            return Transformer::ok(
                'Success to get food details.',
                [
                    'food' => new FoodResource($food)
                ]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get food details.');
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
        $rules = Food::validationRules();
        unset($rules['image']);
        $this->validate($request, $rules);

        try {
            $food =Food::findOrFail($id);
            $food->update(
                $request->only('name', 'description', 'price', 'discount')
            );

            return Transformer::ok(
                'Success to update food data.',
                [
                    'food' => new FoodResource($food)
                ]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update food data.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function updateImage(Request $request, $id)
    {
        $this->validate($request, [
            'image' => Food::validationRules()['image']
        ]);

        try {
            $food =Food::findOrFail($id);

            // Delete image.
            $this->deleteImage($food->image);

            // Update image
            $food->update([
                'image' => $this->storeImage($request->file('image'))
            ]);

            return Transformer::ok(
                'Success to update food image.',
                [
                    'food' => new FoodResource($food)
                ]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update food image.');
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
            $food =Food::findOrFail($id);

            // Delete image.
            $this->deleteImage($food->image);

            // Delete object.
            $food->delete();

            return Transformer::ok(
                'Success to delete food data.',
                [
                    'food' => new FoodResource($food)
                ]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete food data.');
        }
    }
}
