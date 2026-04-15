<?php

namespace App\Http\Requests\Cocktail;

use App\Enums\Unit;
use App\Models\Cocktail;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * @property Cocktail $cocktail
 */
class CocktailUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user()->can('update', $this->cocktail)){
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:60', Rule::unique('cocktails')->ignore($this->cocktail)],
            'description' => ['nullable', 'string', 'min:4', 'max:255'],
            'isPublic' => ['required', 'boolean'],

            'steps' => ['required', 'array', 'min:1', 'max:5'],
            'steps.*.stepNumber' => ['required', 'integer', 'min:1', 'max:15', 'distinct'],
            'steps.*.instruction' => ['required', 'string', 'min:4', 'max:255'],

            'ingredients' => ['required', 'array', 'min:1', 'max:20'],
            'ingredients.*.id' => ['required', 'integer', 'distinct','exists:ingredients,id'],
            'ingredients.*.amount' => ['required', 'numeric', 'min:0.1', 'max:1000'],
            'ingredients.*.overwriteUnit' => ['nullable', new Enum(Unit::class)],

            'categoryIds' => ['required', 'array', 'min:1', 'max:5'],
            'categoryIds.*' => ['distinct', 'exists:categories,id']
        ];
    }
}
