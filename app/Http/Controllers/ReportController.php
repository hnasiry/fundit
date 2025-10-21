<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Banking\Repositories\TransactionRepositoryInterface;
use App\Http\Resources\TopUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class ReportController extends Controller
{
    public function __construct(private readonly TransactionRepositoryInterface $transactionRepository)
    {
    }

    #[OA\Get(
        path: '/api/reports/top-users',
        summary: 'List users with the highest transaction volume',
        tags: ['Reports'],
        security: [
            ['sanctum' => []],
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Top users retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'top_users',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/TopUser')
                        ),
                    ]
                )
            ),
            new OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthenticated'),
        ]
    )]
    public function topUsers(): JsonResponse
    {
        $users = $this->transactionRepository->getTopUsersWithRecentTransactions(3, 10);

        return response()->json([
            'top_users' => TopUserResource::collection($users)->resolve(),
        ]);
    }
}
