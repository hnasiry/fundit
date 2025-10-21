<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin \App\Models\User */
#[OA\Schema(
    schema: 'TopUser',
    required: ['user_id', 'name', 'total_transactions', 'latest_transactions'],
    properties: [
        new OA\Property(property: 'user_id', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'total_transactions', type: 'integer', format: 'int64'),
        new OA\Property(
            property: 'latest_transactions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Transaction')
        ),
    ]
)]
final class TopUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => (string) $this->id,
            'name' => $this->name,
            'total_transactions' => (int) ($this->total_transactions ?? 0),
            'latest_transactions' => TransactionResource::collection($this->whenLoaded('recentTransactions')),
        ];
    }
}
