<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use function now;

final class DemoDataSeeder extends Seeder
{
    public const TEST_USER_EMAIL = 'apitester@example.com';
    public const TEST_USER_PASSWORD = 'Password123!';

    public function run(): void
    {
        $tester = User::query()->updateOrCreate(
            ['email' => self::TEST_USER_EMAIL],
            [
                'name' => 'API Tester',
                'phone' => '09000000000',
                'email_verified_at' => now(),
                'password' => Hash::make(self::TEST_USER_PASSWORD),
            ]
        );

        $primaryAccount = $tester->accounts()->firstOrCreate(['name' => 'Everyday Account']);
        $savingsAccount = $tester->accounts()->firstOrCreate(['name' => 'Savings Account']);

        $primaryCard = $primaryAccount->cards()->updateOrCreate(
            ['number' => '5555444433331111'],
            ['balance' => 1_000_000]
        );

        $backupCard = $primaryAccount->cards()->updateOrCreate(
            ['number' => '5555444433332222'],
            ['balance' => 500_000]
        );

        $savingsAccount->cards()->updateOrCreate(
            ['number' => '5555444433333333'],
            ['balance' => 2_500_000]
        );

        $recipientUsers = User::factory()->count(3)->create();

        foreach ($recipientUsers as $index => $recipient) {
            $account = Account::query()->firstOrCreate([
                'user_id' => $recipient->id,
                'name' => $recipient->name . "'s Account",
            ]);

            $cardNumber = '5556' . str_pad((string) ($recipient->id + 1000), 12, '0', STR_PAD_LEFT);

            $recipientCard = Card::query()->updateOrCreate(
                ['number' => $cardNumber],
                [
                    'account_id' => $account->id,
                    'balance' => 200_000 + ($index * 50_000),
                ]
            );

            Transaction::query()->create([
                'reference_number' => 'TRX-' . Str::upper(Str::random(10)),
                'source_card_id' => $primaryCard->id,
                'destination_card_id' => $recipientCard->id,
                'source_user_id' => $tester->id,
                'destination_user_id' => $recipient->id,
                'amount' => 75_000 + ($index * 25_000),
            ]);

            Transaction::query()->create([
                'reference_number' => 'TRX-' . Str::upper(Str::random(10)),
                'source_card_id' => $backupCard->id,
                'destination_card_id' => $recipientCard->id,
                'source_user_id' => $tester->id,
                'destination_user_id' => $recipient->id,
                'amount' => 25_000 + ($index * 10_000),
            ]);
        }
    }
}
