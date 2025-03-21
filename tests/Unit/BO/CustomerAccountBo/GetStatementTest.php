<?php

namespace Tests\Unit\BO\CustomerAccountBo;

use App\BO\CustomerAccountBo;
use App\Exceptions\AccountNotFoundException;
use App\Interfaces\AccountNumberGeneratorInterface;
use App\Models\CustomerAccount;
use App\Models\Transaction;
use App\Repositories\CustomerAccountRepository;
use App\Resources\CustomerAccountData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class GetStatementTest extends TestCase
{
    protected $customerAccountRepositoryMock;
    protected $customerAccountDataMock;
    protected $accountNumberGeneratorMock;
    protected $customerAccountBo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerAccountRepositoryMock = Mockery::mock(CustomerAccountRepository::class);
        $this->customerAccountDataMock = Mockery::mock(CustomerAccountData::class);
        $this->accountNumberGeneratorMock = Mockery::mock(AccountNumberGeneratorInterface::class);

        $this->customerAccountBo = new CustomerAccountBo(
            $this->customerAccountRepositoryMock,
            $this->customerAccountDataMock,
            $this->accountNumberGeneratorMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetStatementSuccess()
    {
        $accountNumber = '123.456.7890-1';
        $balance = 250.00;
        $startDate = '2023-01-01';
        $endDate = '2023-01-31';

        $request = new Request([
            'account_number' => $accountNumber,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $account = new CustomerAccount();
        $account->id = 1;
        $account->number_account = $accountNumber;
        $account->balance = $balance;

        $transaction1 = new Transaction();
        $transaction1->id = 1;
        $transaction1->account_id = 1;
        $transaction1->type = 'deposit';
        $transaction1->amount = 100.00;
        $transaction1->description = 'Test deposit';
        $transaction1->balance_before = 150.00;
        $transaction1->balance_after = 250.00;
        $transaction1->created_at = Carbon::parse('2023-01-15');

        $transaction2 = new Transaction();
        $transaction2->id = 2;
        $transaction2->account_id = 1;
        $transaction2->type = 'withdrawal';
        $transaction2->amount = -50.00;
        $transaction2->description = 'Test withdrawal';
        $transaction2->balance_before = 300.00;
        $transaction2->balance_after = 250.00;
        $transaction2->created_at = Carbon::parse('2023-01-20');

        $transactions = new Collection([$transaction1, $transaction2]);

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($accountNumber)
            ->andReturn($account);

        $this->customerAccountRepositoryMock->shouldReceive('getTransactions')
            ->once()
            ->with($account->id, $startDate, $endDate)
            ->andReturn($transactions);

        $this->customerAccountRepositoryMock->shouldReceive('getAccountNumberById')
            ->times(0); // Já que não há related_account_id nos dados de teste

        $result = $this->customerAccountBo->getStatement($request);

        $this->assertIsArray($result);
        $this->assertEquals($accountNumber, $result['account_number']);
        $this->assertEquals($balance, $result['current_balance']);
        $this->assertArrayHasKey('period', $result);
        $this->assertEquals($startDate, $result['period']['start_date']);
        $this->assertEquals($endDate, $result['period']['end_date']);
        $this->assertArrayHasKey('transactions', $result);
        $this->assertCount(2, $result['transactions']);
    }

    public function testGetStatementWithDefaultDates()
    {
        $accountNumber = '123.456.7890-1';
        $balance = 250.00;

        $request = new Request([
            'account_number' => $accountNumber
        ]);

        $account = new CustomerAccount();
        $account->id = 1;
        $account->number_account = $accountNumber;
        $account->balance = $balance;

        $transactions = new Collection([]);

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($accountNumber)
            ->andReturn($account);

        $this->customerAccountRepositoryMock->shouldReceive('getTransactions')
            ->once()
            ->withArgs(function ($accountId, $startDate, $endDate) {
                return $accountId === 1 &&
                       $startDate === now()->subDays(30)->format('Y-m-d') &&
                       $endDate === now()->format('Y-m-d');
            })
            ->andReturn($transactions);

        $result = $this->customerAccountBo->getStatement($request);

        $this->assertIsArray($result);
        $this->assertEquals($accountNumber, $result['account_number']);
        $this->assertEquals($balance, $result['current_balance']);
        $this->assertArrayHasKey('period', $result);
        $this->assertArrayHasKey('transactions', $result);
        $this->assertCount(0, $result['transactions']);
    }

    public function testGetStatementWithNonExistentAccount()
    {
        $accountNumber = '999.999.9999-9';
        $request = new Request([
            'account_number' => $accountNumber
        ]);

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($accountNumber)
            ->andReturn(null);

        $this->expectException(AccountNotFoundException::class);
        $this->customerAccountBo->getStatement($request);
    }
}
