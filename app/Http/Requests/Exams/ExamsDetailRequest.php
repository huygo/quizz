<?php

namespace App\Http\Requests\Exams;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExamsDetailRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => 'required|numeric|min:1',
            'per_page' => 'required|numeric|min:1|max:100',
            'exam_id' => 'required|numeric|min:1',
        ];
    }

    /**
     * response error validate
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 422,
            'success' => false,
            'message' => $validator->errors()->getMessages(),
        ], 200));
    }
}
