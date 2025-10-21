<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Services\Transactions\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class TransactionController extends Controller
{
    public function __construct(private readonly TransferService $transferService)
    {
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $transaction = $this->transferService->transfer(
            $request->string('source_card')->toString(),
            $request->string('destination_card')->toString(),
            (int) $request->integer('amount')
        );

        return response()->json([
            'success' => true,
            'reference_number' => $transaction->reference_number,
        ]);
    }
}
