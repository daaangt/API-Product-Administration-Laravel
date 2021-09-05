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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::All();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Product::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
