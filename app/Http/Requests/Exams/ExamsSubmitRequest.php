<?php

namespace App\Http\Requests\Exams;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExamsSubmitRequest extends FormRequest
{
    public function rules()
    {
        return [
            'exam_id' => 'required|numeric|min:1',
            'answer' => 'required|array',
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
