<?php

namespace App\Http\Requests\Cocktail;

use App\Support\Cocktail\CocktailQueryHelper;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CocktailShowRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'include' => ['sometimes', 'array', 'min:1'],
            'include.*' => ['string', Rule::in(CocktailQueryHelper::allowedRelationShips()), 'distinct'],
        ];
    }
}
