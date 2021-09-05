<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoriesController extends Controller
{
    /**
     * @OA\Get(
     *   path="/categories",
     *   summary="Display a list of categories",
     *   operationId="indexCategory",
     *   tags={"Categories"},*
     *   @OA\Response(
     *     response=200,
     *     description="A list with all the categories"
     *   )
     * )
     */
    public function index()
    {
        return Category::All();
    }

    /**
     * @OA\Get(
     *   path="/categories/{id}",
     *   summary="Display a specific category",
     *   operationId="showCategory",
     *   tags={"Categories"},
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *         type="number"
     *      )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Display a specific category"
     *   )
     * )
     */
    public function show($id)
    {
        return Category::find($id);
    }

    /**
     * @OA\Post(
     *    path="/categories",
     *    summary="Store a new category",
     *    description="Store a category",
     *    operationId="storeCategory",
     *    tags={"Categories"},
     *    security={{ "bearerAuth": {} }},
     *    @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *             required={"name", "file"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="file", type="file", format="file"),
     *          )
     *       )
     *    ),
     *    @OA\Response(
     *       response=201,
     *       description="Category stored!",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="string", example="Category stored!")
     *       )
     *    ),
     *    @OA\Response(
     *       response=422,
     *       description="Missing informations!",
     *       @OA\JsonContent(
     *          @OA\Property(property="error", type="string", example="Missing informations!")
     *       )
     *    ),
     *    @OA\Response(
     *       response=401,
     *       description="Unauthorized!",
     *       @OA\JsonContent(
     *          @OA\Property(property="error", type="string", example="Unauthorized!")
     *       )
     *    ),
     * )
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $request->validated();

            if ($request->hasFile('file')) {
                $request->file('file')->move(public_path('categories\\'), $request->name . "." . $request->file('file')->getClientOriginalExtension());
                $category['file'] = 'categories\\' . $request->name . "." . $request->file('file')->getClientOriginalExtension();
            }

            Category::create($category);
        } catch (\Exception $e) {
            if (env('APP_DEBUG')) {
                throw new HttpResponseException(response()->json(['error' => $e->getMessage()], 500));
            } else {
                throw new HttpResponseException(response()->json(['error' => 'An error ocurred, in the store category attempt!'], 500));
            }
        }
        return response()->json(['sucess' => 'Category stored correctly!'], 201);
    }

    /**
     * @OA\PUT(
     *    path="/categories/{id}",
     *    summary="Update an existing category",
     *    description="Update a category",
     *    operationId="updateCategory",
     *    tags={"Categories"},
     *    security={{ "bearerAuth": {} }},
     *    @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="number"
     *       )
     *    ),
     *    @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="file", type="file", format="file"),
     *          )
     *       )
     *    ),
     *    @OA\Response(
     *       response=200,
     *       description="Category updated!",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="string", example="Category updated!")
     *       )
     *    ),
     *    @OA\Response(
     *       response=422,
     *       description="Category updated!",
     *       @OA\JsonContent(
     *          @OA\Property(property="error", type="string", example="Missing informations!")
     *       )
     *    ),
     *    @OA\Response(
     *       response=401,
     *       description="Unauthorized!",
     *       @OA\JsonContent(
     *          @OA\Property(property="error", type="string", example="Unauthorized!")
     *       )
     *    )
     * )
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $category = Category::find($id);

            $updatedCategory = $request->validated();

            if ($request->hasFile('file')) {

                if (!is_null($category->file) && file_exists(public_path($category->file))) {
                    unlink(public_path($category->file));
                }

                $request->file('file')->move(public_path('categories\\'), $request->name . "." . $request->file('file')->getClientOriginalExtension());
                $updatedCategory['file'] = 'categories\\' . $request->name . "." . $request->file('file')->getClientOriginalExtension();
            }

            $category->update($updatedCategory);
        } catch (\Exception $e) {
            if (env('APP_DEBUG')) {
                throw new HttpResponseException(response()->json(['error' => $e->getMessage()], 500));
            } else {
                throw new HttpResponseException(response()->json(['error' => 'An error ocurred, in the update category attempt!'], 500));
            }
        }
        return response()->json(['sucess' => 'Category updated correctly!'], 200);
    }

    /**
     * @OA\DELETE(
     *    path="/categories/{id}",
     *    summary="Delete an existing category",
     *    description="Delete a category",
     *    operationId="deleteCategory",
     *    tags={"Categories"},
     *    security={{ "bearerAuth": {} }},
     *    @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *    ),
     *    @OA\Response(
     *       response=200,
     *       description="Category deleted!",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="string", example="Category deleted!")
     *       )
     *    ),
     *    @OA\Response(
     *       response=401,
     *       description="Unauthorized!",
     *       @OA\JsonContent(
     *          @OA\Property(property="error", type="string", example="Unauthorized!")
     *       )
     *    ),
     * )
     */
    public function destroy($id)
    {
        try {
            $category = Category::find($id);

            if (!is_null($category->file) && file_exists(public_path($category->file))) {
                unlink(public_path($category->file));
            }

            $category->delete();
        } catch (\Exception $e) {
            if (env('APP_DEBUG')) {
                throw new HttpResponseException(response()->json(['error' => $e->getMessage()], 500));
            } else {
                throw new HttpResponseException(response()->json(['error' => 'An error ocurred, in the delete category attempt!'], 500));
            }
        }

        return response()->json(['sucess' => 'Category deleted correctly!'], 200);
    }
}
