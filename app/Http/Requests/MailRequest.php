<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
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
            'emails.*.attachments.*.filename' => 'required|string|min:1',
            'emails.*.attachments.*.file_data' => 'required|string|min:1',
        ];
    }
}
