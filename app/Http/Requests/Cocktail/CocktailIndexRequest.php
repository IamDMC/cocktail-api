<?php

namespace App\Http\Requests\Cocktail;

use App\Support\Cocktail\CocktailQueryHelper;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CocktailIndexRequest extends FormRequest
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
            'per_page' => ['integer', 'min:1', 'max:100'],
            'limit' =>  ['integer', 'min:1', 'max:100'],
            'search' => ['string', 'max:60'],

            'include' => ['sometimes', 'array', 'min:1'],
            'include.*' => ['string', Rule::in(CocktailQueryHelper::allowedRelationShips()), 'distinct'],

            'filter' => ['sometimes', 'array', 'min:1'],
            'filter.*.name' => ['required', 'string', Rule::in(CocktailQueryHelper::availableFilters())],
            'filter.*.values' => ['required', 'array', 'min:1'],
            'filter.*.values.*' => ['required', 'integer', 'min:1', 'distinct']

        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $filters = $this->input('filter', []);

            $names = collect($filters)->pluck('name');

            if ($names->duplicates()->isNotEmpty()) {
                $validator->errors()->add(
                    'filter',
                    'Duplicate filters are not allowed.'
                );
            }
        });
    }

}
