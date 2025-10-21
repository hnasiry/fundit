<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
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
