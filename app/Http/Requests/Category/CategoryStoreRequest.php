<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @property Category $category
 */
class CategoryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user()->can('create', Category::class)){
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
            'name' => ['required', 'string', 'min:4', 'max:60', 'unique:categories,name'],
            'description' => ['required', 'string', 'min:4', 'max:255']
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'description' => 'Beschreibung',
        ];
    }
}
