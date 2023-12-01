<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'url' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'event_id' => 'required'
        ];
    }
        /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Thiếu tên của tài nguyên sự kiện',
            'url.required' => 'Chưa nhập ảnh tài nguyên',
            'event_id.required' => 'Chưa nhập id của tài nguyên'
        ];
    }
}
