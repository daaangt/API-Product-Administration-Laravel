<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Exceptions\HttpResponseException;


class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Category::All();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Category::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
