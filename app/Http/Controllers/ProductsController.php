<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * @OA\Get(
     *    path="/products",
     *    summary="Display a list of products",
     *    operationId="index",
     *    tags={"Products"},*
     *    @OA\Response(
     *       response=200,
     *       description="A list with all the products"
     *    )
     * )
     */
    public function index()
    {
        return Product::All();
    }

    /**
     * @OA\Get(
     *    path="/products/{id}",
     *    summary="Display a specific products",
     *    operationId="show",
     *    tags={"Products"},
     *    @OA\Response(
     *       response=200,
     *       description="Display a specific products"
     *    )
     * )
     */
    public function show($id)
    {
        return Product::find($id);
    }

    /**
     * @OA\Post(
     *    path="/products",
     *    summary="Store a new product",
     *    description="Store a product",
     *    operationId="store",
     *    tags={"Products"},
     *    security={{ "bearerAuth": {} }},
     *    @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *          required={"name", "categories_id", "quantity", "price", "size", "composition", "file[]"},
     *             @OA\Property(property="name", type="string", format="text", example="ProductName"),
     *             @OA\Property(property="categories_id", type="number", example="1"),
     *             @OA\Property(property="quantity", type="number", example="15"),
     *             @OA\Property(property="price", type="double", format="double", example="15.50"),
     *             @OA\Property(property="size", type="string", format="text", example="PP"),
     *             @OA\Property(property="composition", type="string", format="text", example="CompositionInformations"),
     *             @OA\Property(property="file[]", type="file", format="file[]", example="")
     *          )
     *       )
     *    ),
     *    @OA\Response(
     *       response=201,
     *       description="Product stored!",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="string", example="Product stored!")
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
     *    )
     * )
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $products = $request->validated();

            $products['file'] = [];
            $file_count = 0;
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $file) {
                    if ($file->isValid()) {
                        $file->move(public_path('products\\'), $request->name . "_" . ++$file_count . "." . $file->getClientOriginalExtension());
                        array_push($products['file'], 'products\\' . $request->name . "_" . $file_count . "." . $file->getClientOriginalExtension());
                    }
                }
            }

            Product::create($products);
        } catch (\Exception $e) {
            if (env('APP_DEBUG')) {
                throw new HttpResponseException(response()->json(['error' => $e->getMessage()], 500));
            } else {
                throw new HttpResponseException(response()->json(['error' => 'An error ocurred, in the store product attempt!'], 500));
            }
        }
        return response()->json(['sucess' => 'Product stored correctly!'], 201);
    }

    /**
     * @OA\Put(
     *    path="/products/{id}",
     *    summary="Update an existing product",
     *    description="Update a product",
     *    operationId="update",
     *    tags={"Products"},
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
     *             @OA\Property(property="name", type="string", format="text", example="ProductName"),
     *             @OA\Property(property="categories_id", type="number", example="1"),
     *             @OA\Property(property="quantity", type="number", example="15"),
     *             @OA\Property(property="price", type="double", format="double", example="15.50"),
     *             @OA\Property(property="size", type="string", format="text", example="PP"),
     *             @OA\Property(property="composition", type="string", format="text", example="CompositionInformations"),
     *             @OA\Property(property="file[]", type="file", format="file[]", example="")
     *          )
     *       )
     *    ),
     *    @OA\Response(
     *       response=201,
     *       description="Product updated!",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="string", example="Product updated!")
     *        )
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
    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $product = Product::find($id);

            $updatedProduct = $request->validated();

            if ($request->hasFile('file')) {
                $updatedProduct['file'] = [];
                $file_count = 0;

                if (!is_null($product->file)) {
                    foreach ($product->file as $old_img) {
                        if (file_exists(public_path($old_img))) {
                            unlink(public_path($old_img));
                        }
                    }
                }

                foreach ($request->file('file') as $file) {
                    if ($file->isValid()) {
                        $file->move(public_path('products\\'), $request->name . "_" . ++$file_count . "." . $file->getClientOriginalExtension());
                        array_push($updatedProduct['file'], 'products\\' . $request->name . "_" . $file_count . "." . $file->getClientOriginalExtension());
                    }
                }
            } else {
                unset($updatedProduct['file']);
            }

            $product->update($updatedProduct);
        } catch (\Exception $e) {
            if (env('APP_DEBUG')) {
                throw new HttpResponseException(response()->json(['error' => $e->getMessage()], 500));
            } else {
                throw new HttpResponseException(response()->json(['error' => 'An error ocurred, in the update product attempt!'], 500));
            }
        }
        return response()->json(['sucess' => 'Product updated correctly!'], 201);
    }

    /**
     * @OA\DELETE(
     *    path="/products/{id}",
     *    summary="Delete an existing product",
     *    description="Delete a product",
     *    operationId="delete",
     *    tags={"Products"},
     *    security={{ "bearerAuth": {} }},
     *    @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *    ),
     *    @OA\Response(
     *       response=200,
     *       description="product deleted!",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="string", example="product deleted!")
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
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!is_null($product->file)) {
            foreach ($product->file as $file) {
                if (file_exists(public_path($file))) {
                    unlink(public_path($file));
                }
            }
        }

        $product->delete();

        return response()->json(['sucess' => 'Product deleted correctly!'], 201);
    }
}
