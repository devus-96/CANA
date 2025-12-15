<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Rules\PhoneNumber;

class MemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'first_name'    => 'string|required',
                'last_name'     => 'string|required',
                'gender'        => 'required',
                'email'         => 'required|string|lowercase|email|max:255|unique:'.User::class,
                "role"          => 'required|string',
                'phone'         => ['required|string|unique:'.User::class, new PhoneNumber],
                'date_of_birth' => 'date|before:today',
                'profile'       => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
                "stateOfLive"     => "nullable|string",
                "city"          => "nullable|string",
                "parish"        => "nullable|string"
        ];
    }
}
