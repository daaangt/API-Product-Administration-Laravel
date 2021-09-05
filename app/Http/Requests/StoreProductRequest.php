<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:3',
            'categories_id' => 'required|integer',
            'quantity' => 'required|integer',
            'price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'size' => ['required', Rule::in(['PP', 'P', 'M', 'G', 'GG', 'EG', 'EGG'])],
            'composition' => 'required|min:3|max:1000',
            'file.*' => 'mimes:jpeg,jpg,png|max:5000',
            'file' => 'required|max:3|array'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Invalid data send',
            'details' => $errors->messages(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
