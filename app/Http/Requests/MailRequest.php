<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MailRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'emails' => 'required|array|min:1|max:200',
            'emails.*.to' => 'required|email',
            'emails.*.subject' => 'required|string|min:1',
            'emails.*.body' => 'required|string|min:1|max:384000',
            'emails.*.attachments' => 'sometimes|array',
            'emails.*.attachments.*.filename' => 'required|string|min:1|regex:/^[\w,\s-]+\.[A-Za-z]{3}$/',
            'emails.*.attachments.*.file_data' => 'required|string|min:1',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array|void
     */
    public function messages() {
        return [
            'emails.*.to.email' => "':input' is not a valid email",
            'emails.*.attachments.*.filename.regex' => "':input' is not a valid filename.",
        ];
    }
}
