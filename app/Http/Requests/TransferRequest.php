<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'source_card' => ['required', 'digits:16'],
            'destination_card' => ['required', 'digits:16'],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }
}
