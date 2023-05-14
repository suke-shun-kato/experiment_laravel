<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;

class RecipeStoreRequest extends RecipeUpdateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['title'][] = 'required';
        $rules['description'][] = 'required';

        return $rules;
    }
}
