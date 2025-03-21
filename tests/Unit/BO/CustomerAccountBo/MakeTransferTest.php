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

class MakeTransferTest extends TestCase
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

    public function testMakeTransferSuccess()
    {
        $sourceAccountNumber = '123.456.7890-1';
        $targetAccountNumber = '987.654.3210-9';
        $sourceCurrentBalance = 100.00;
        $targetCurrentBalance = 50.00;
        $amount = 30.00;
        $sourceNewBalance = $sourceCurrentBalance - $amount;
        $targetNewBalance = $targetCurrentBalance + $amount;

        $request = new Request([
            'source_account_number' => $sourceAccountNumber,
            'target_account_number' => $targetAccountNumber,
            'amount' => $amount,
            'description' => 'Transferência de teste'
        ]);

        $sourceAccount = new CustomerAccount();
        $sourceAccount->id = 1;
        $sourceAccount->number_account = $sourceAccountNumber;
        $sourceAccount->balance = $sourceCurrentBalance;
        $sourceAccount->user_id = 1;
        $sourceAccount->agency = '0001';
        $sourceAccount->type_account = 'checking';
        $sourceAccount->status = 'active';

        $targetAccount = new CustomerAccount();
        $targetAccount->id = 2;
        $targetAccount->number_account = $targetAccountNumber;
        $targetAccount->balance = $targetCurrentBalance;
        $targetAccount->user_id = 2;
        $targetAccount->agency = '0001';
        $targetAccount->type_account = 'checking';
        $targetAccount->status = 'active';

        DB::shouldReceive('beginTransaction')->once();

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($sourceAccountNumber)
            ->andReturn($sourceAccount);

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($targetAccountNumber)
            ->andReturn($targetAccount);

        $this->customerAccountRepositoryMock->shouldReceive('updateBalance')
            ->once()
            ->with(1, $sourceNewBalance)
            ->andReturn(true);

        $this->customerAccountRepositoryMock->shouldReceive('updateBalance')
            ->once()
            ->with(2, $targetNewBalance)
            ->andReturn(true);

        $this->customerAccountRepositoryMock->shouldReceive('createTransaction')
            ->twice()
            ->andReturn((object)['id' => 1], (object)['id' => 2]);

        $this->customerAccountDataMock->shouldReceive('setNumberAccount')
            ->twice()
            ->andReturnSelf();
        $this->customerAccountDataMock->shouldReceive('setIdUser')
            ->twice()
            ->andReturnSelf();
        $this->customerAccountDataMock->shouldReceive('setTypeAccount')
            ->twice()
            ->andReturnSelf();
        $this->customerAccountDataMock->shouldReceive('setBalance')
            ->twice()
            ->andReturnSelf();
        $this->customerAccountDataMock->shouldReceive('setStatus')
            ->twice()
            ->andReturnSelf();
        $this->customerAccountDataMock->shouldReceive('setAgency')
            ->twice()
            ->andReturnSelf();

        DB::shouldReceive('commit')->once();

        $result = $this->customerAccountBo->makeTransfer($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('source_account', $result);
        $this->assertArrayHasKey('target_account', $result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertEquals($amount, $result['amount']);
    }

    public function testMakeTransferWithInsufficientFunds()
    {
        $sourceAccountNumber = '123.456.7890-1';
        $targetAccountNumber = '987.654.3210-9';
        $sourceCurrentBalance = 20.00;
        $amount = 30.00;

        $request = new Request([
            'source_account_number' => $sourceAccountNumber,
            'target_account_number' => $targetAccountNumber,
            'amount' => $amount
        ]);

        $sourceAccount = new CustomerAccount();
        $sourceAccount->id = 1;
        $sourceAccount->number_account = $sourceAccountNumber;
        $sourceAccount->balance = $sourceCurrentBalance;

        $targetAccount = new CustomerAccount();
        $targetAccount->id = 2;
        $targetAccount->number_account = $targetAccountNumber;
        $targetAccount->balance = 50.00;

        DB::shouldReceive('beginTransaction')->once();

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($sourceAccountNumber)
            ->andReturn($sourceAccount);

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($targetAccountNumber)
            ->andReturn($targetAccount);

        DB::shouldReceive('rollBack')->once();

        $this->expectException(InsufficientFundsException::class);

        $this->customerAccountBo->makeTransfer($request);
    }

    public function testMakeTransferToSameAccount()
    {
        $accountNumber = '123.456.7890-1';
        $amount = 50.00;

        $request = new Request([
            'source_account_number' => $accountNumber,
            'target_account_number' => $accountNumber,
            'amount' => $amount
        ]);

        $account = new CustomerAccount();
        $account->id = 1;
        $account->number_account = $accountNumber;
        $account->balance = 100.00;

        DB::shouldReceive('beginTransaction')->once();

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($accountNumber)
            ->andReturn($account);

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($accountNumber)
            ->andReturn($account);

        DB::shouldReceive('rollBack')->once();

        $this->expectException(InvalidTransactionException::class);
        $this->expectExceptionMessage("Não é possível transferir para a mesma conta");

        $this->customerAccountBo->makeTransfer($request);
    }

    public function testMakeTransferWithNegativeAmount()
    {
        $request = new Request([
            'source_account_number' => '123.456.7890-1',
            'target_account_number' => '987.654.3210-9',
            'amount' => -50.00
        ]);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $this->expectException(InvalidTransactionException::class);
        $this->expectExceptionMessage("O valor da transferência deve ser maior que zero");

        $this->customerAccountBo->makeTransfer($request);
    }

    public function testMakeTransferWithNonExistentSourceAccount()
    {
        $sourceAccountNumber = '999.999.9999-9';
        $targetAccountNumber = '987.654.3210-9';
        $amount = 50.00;

        $request = new Request([
            'source_account_number' => $sourceAccountNumber,
            'target_account_number' => $targetAccountNumber,
            'amount' => $amount
        ]);

        DB::shouldReceive('beginTransaction')->once();

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($sourceAccountNumber)
            ->andReturn(null);

        DB::shouldReceive('rollBack')->once();

        $this->expectException(AccountNotFoundException::class);

        $this->customerAccountBo->makeTransfer($request);
    }
}