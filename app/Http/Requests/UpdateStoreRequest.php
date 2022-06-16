<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStoreRequest extends FormRequest
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
            'user_id' => 'nullable|integer',
            'name' => 'nullable|string|alpha_num',
            'description' => 'nullable|min:1|max:500',
            'address' => 'nullable|regex:/([- ,\/0-9a-zA-Z]+)/',
            'phone_number' => 'nullable|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{3})/',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|alpha',
            'rating' => 'nullable|numeric',
        ];
    }

    /**
     * @param Validator $validator
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message'=>'Validation errors',
            'data' => $validator->errors()
        ]));
    }
}
