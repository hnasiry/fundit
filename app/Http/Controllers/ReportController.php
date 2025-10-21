<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\TopUserResource;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class ReportController extends Controller
{
    public function __construct(private readonly TransactionRepositoryInterface $transactionRepository)
    {
    }

    public function topUsers(): JsonResponse
    {
        $users = $this->transactionRepository->getTopUsersWithRecentTransactions(3, 10);

        return response()->json([
            'top_users' => TopUserResource::collection($users)->resolve(),
        ]);
    }
}
