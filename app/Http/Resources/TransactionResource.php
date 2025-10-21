<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin \App\Models\Transaction */
#[OA\Schema(
    schema: 'Transaction',
    required: ['reference_number', 'source_card', 'destination_card', 'amount', 'created_at'],
    properties: [
        new OA\Property(property: 'reference_number', type: 'string'),
        new OA\Property(property: 'source_card', type: 'string'),
        new OA\Property(property: 'destination_card', type: 'string'),
        new OA\Property(property: 'amount', type: 'integer', format: 'int64'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
final class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'reference_number' => $this->reference_number,
            'source_card' => $this->sourceCard?->masked_number,
            'destination_card' => $this->destinationCard?->masked_number,
            'amount' => $this->amount,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
