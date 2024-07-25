<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required', 'string', 'max:250',
                Rule::unique(Employee::class, 'name')->ignore($this->id)
            ],

            'internal_phone' => [
                'sometimes', 'nullable', 'string', 'max:255',
                Rule::unique(Employee::class, 'internal_phone')->ignore($this->id)
            ],

            'remonline_login' => [
                'sometimes', 'nullable', 'string', 'max:255',
                Rule::unique(Employee::class, 'remonline_login')->ignore($this->id)
            ] ,

            'tg_login'=> [
                'sometimes', 'nullable', 'string', 'max:255',
                Rule::unique(Employee::class, 'tg_login')->ignore($this->id)
            ],
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
