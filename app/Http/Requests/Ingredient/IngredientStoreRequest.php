<?php

namespace App\Http\Requests\Ingredient;

use App\Enums\Unit;
use App\Models\Ingredient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class IngredientStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user()->can('create', Ingredient::class)){
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
            'name' => ['required', 'string', 'min:4', 'max:60', 'unique:ingredients,name'],
            'description' => ['nullable', 'string', 'min:4', 'max:255'],
            'default_unit' => ['required', new Enum(Unit::class)]
        ];
    }
}
