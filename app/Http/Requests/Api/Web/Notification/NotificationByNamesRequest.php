<?php

namespace App\Http\Requests\Api\Web\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationByNamesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'names' => 'required|array',
        ];
    }
}
