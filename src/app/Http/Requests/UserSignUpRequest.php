<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;

class UserSignUpRequest extends UserLoginRequest
{

    /**
     * バリデーションルールの配列を返す
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();


        return array_merge($rules, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            ]
        );
    }
}
