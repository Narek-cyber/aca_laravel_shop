<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
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
            'firstname' => 'required|string|between:2,100',
            'type' => 'required|in:' . implode(',', array_keys(User::TYPE_SLUGES)),
//            'type' => ['required', Rule::in(array_keys(User::TYPE_SLUGES))],
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ];
//        return [
//            'firstname' => 'required|string|min:2|max:255',
//            'lastname' => 'string|min:3|max:255',
//            "email" => 'required|email:rfc|unique:users,email',
//            'password' => 'required|confirmed|string|min:5|max:255',
//            'type' => [
//                'required',
//                Rule::in([User::TYPE_BUYER, User::TYPE_SELLER])
//            ],
//        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ]));
    }
}
