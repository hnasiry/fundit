<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Banking\DTO\TransferData;
use App\Domain\Banking\Repositories\CardRepositoryInterface;
use App\Application\Banking\Services\TransferFundsService;
use App\Exceptions\CardNotFoundException;
use App\Http\Requests\TransferRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TransferFundsService $transferFundsService,
        private readonly CardRepositoryInterface $cardRepository,
    ) {
    }

    #[OA\Post(
        path: '/api/transactions/transfer',
        summary: 'Transfer funds between cards',
        tags: ['Transactions'],
        security: [
            ['sanctum' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['source_card', 'destination_card', 'amount'],
                properties: [
                    new OA\Property(property: 'source_card', type: 'string', example: '5555444433331111'),
                    new OA\Property(property: 'destination_card', type: 'string', example: '5555444433333333'),
                    new OA\Property(property: 'amount', type: 'integer', format: 'int64', example: 25000),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Transfer completed successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean'),
                        new OA\Property(property: 'reference_number', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'Validation error'),
            new OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthenticated'),
        ]
    )]
    public function transfer(TransferRequest $request): JsonResponse
    {
        $sourceCard = $this->cardRepository->findByNumber($request->get('source_card'));
        if ($sourceCard === null) {
            throw new CardNotFoundException();
        }

        $this->authorize('transfer', $sourceCard);

        $transaction = $this->transferFundsService->handle(
            new TransferData(
                $request->string('source_card')->toString(),
                $request->string('destination_card')->toString(),
                (int) $request->integer('amount')
            )
        );

        return response()->json([
            'success' => true,
            'reference_number' => $transaction->reference_number,
        ]);
    }
}
