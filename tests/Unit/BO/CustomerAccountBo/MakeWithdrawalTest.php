<?php

namespace Tests\Unit\BO\CustomerAccountBo;

use App\BO\CustomerAccountBo;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidTransactionException;
use App\Interfaces\AccountNumberGeneratorInterface;
use App\Models\CustomerAccount;
use App\Repositories\CustomerAccountRepository;
use App\Resources\CustomerAccountData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class MakeWithdrawalTest extends TestCase
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

    public function testMakeWithdrawalSuccess()
    {
        $accountNumber = '123.456.7890-1';
        $currentBalance = 100.00;
        $amount = 50.00;
        $newBalance = $currentBalance - $amount;

        $request = new Request([
            'account_number' => $accountNumber,
            'amount' => $amount,
            'description' => 'Saque de teste'
        ]);

        $account = new CustomerAccount();
        $account->id = 1;
        $account->number_account = $accountNumber;
        $account->balance = $currentBalance;
        $account->user_id = 1;
        $account->agency = '0001';
        $account->type_account = 'checking';
        $account->status = 'active';

        DB::shouldReceive('beginTransaction')->once();

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($accountNumber)
            ->andReturn($account);

        $this->customerAccountRepositoryMock->shouldReceive('updateBalance')
            ->once()
            ->with(1, $newBalance)
            ->andReturn(true);

        $this->customerAccountRepositoryMock->shouldReceive('createTransaction')
            ->once()
            ->withArgs(function ($data) use ($account, $amount, $currentBalance, $newBalance) {
                return $data['account_id'] === $account->id &&
                    $data['type'] === 'withdrawal' &&
                    $data['amount'] === -$amount &&
                    $data['balance_before'] === $currentBalance &&
                    $data['balance_after'] === $newBalance;
            })
            ->andReturn((object)['id' => 1]);

        $this->customerAccountDataMock->shouldReceive('setNumberAccount')
            ->once()
            ->with($accountNumber)
            ->andReturnSelf();
        $this->customerAccountDataMock->shouldReceive('setIdUser')
            ->once()
            ->with(1)
            ->andReturnSelf();
        $this->customerAccountDataMock->shouldReceive('setTypeAccount')
            ->once()
            ->with('checking')
            ->andReturnSelf();
        $this->customerAccountDataMock->shouldReceive('setBalance')
            ->once()
            ->with($newBalance)
            ->andReturnSelf();
        $this->customerAccountDataMock->shouldReceive('setStatus')
            ->once()
            ->with('active')
            ->andReturnSelf();
        $this->customerAccountDataMock->shouldReceive('setAgency')
            ->once()
            ->with('0001')
            ->andReturnSelf();

        DB::shouldReceive('commit')->once();

        $result = $this->customerAccountBo->makeWithdrawal($request);

        $this->assertSame($this->customerAccountDataMock, $result);
    }

    public function testMakeWithdrawalWithInsufficientFunds()
    {
        $accountNumber = '123.456.7890-1';
        $currentBalance = 30.00;
        $amount = 50.00;

        $request = new Request([
            'account_number' => $accountNumber,
            'amount' => $amount
        ]);

        $account = new CustomerAccount();
        $account->id = 1;
        $account->number_account = $accountNumber;
        $account->balance = $currentBalance;

        DB::shouldReceive('beginTransaction')->once();

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($accountNumber)
            ->andReturn($account);

        DB::shouldReceive('rollBack')->once();

        $this->expectException(InsufficientFundsException::class);

        $this->customerAccountBo->makeWithdrawal($request);
    }

    public function testMakeWithdrawalWithNegativeAmount()
    {
        $request = new Request([
            'account_number' => '123.456.7890-1',
            'amount' => -50.00
        ]);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $this->expectException(InvalidTransactionException::class);
        $this->expectExceptionMessage("O valor do saque deve ser maior que zero");

        $this->customerAccountBo->makeWithdrawal($request);
    }

    public function testMakeWithdrawalWithNonExistentAccount()
    {
        $accountNumber = '999.999.9999-9';
        $request = new Request([
            'account_number' => $accountNumber,
            'amount' => 50.00
        ]);

        DB::shouldReceive('beginTransaction')->once();

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($accountNumber)
            ->andReturn(null);

        DB::shouldReceive('rollBack')->once();

        $this->expectException(AccountNotFoundException::class);

        $this->customerAccountBo->makeWithdrawal($request);
    }
}
