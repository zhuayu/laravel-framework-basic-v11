<?php

namespace App\Http\Requests\Api\Web\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'unread' => 'nullable|integer',
            'name' => 'nullable|string',
            'names' => 'nullable|array',
        ];
    }
}
