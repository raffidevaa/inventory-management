<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'code'        => ['required', 'string', 'max:50', "unique:products,code,{$productId}"],
            'name'        => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'stock'       => ['required', 'integer', 'min:0'],
            'location'    => ['nullable', 'string', 'max:100'],
            'condition'   => ['required', 'in:good,lightly_damaged,heavily_damaged'],
            'image'       => ['nullable', 'image', 'max:2048'],
        ];
    }
}
