<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class RecipeUpdateRequest extends FormRequest
{
    /**
     * false だと 403 Forbidden が返る
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['string'],
            'description' => ['string'],
            'u_image_ids' => ['array'],
            'u_image_ids.*' => ['int'],
        ];
    }
}
