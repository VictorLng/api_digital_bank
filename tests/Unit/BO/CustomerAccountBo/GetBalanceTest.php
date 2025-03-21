<?php

namespace Tests\Unit\BO\CustomerAccountBo;

use App\BO\CustomerAccountBo;
use App\Exceptions\AccountNotFoundException;
use App\Interfaces\AccountNumberGeneratorInterface;
use App\Models\CustomerAccount;
use App\Repositories\CustomerAccountRepository;
use App\Resources\CustomerAccountData;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class GetBalanceTest extends TestCase
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

    public function testGetBalanceSuccess()
    {
        $accountNumber = '123.456.7890-1';
        $balance = 150.75;

        $request = new Request([
            'account_number' => $accountNumber
        ]);

        $account = new CustomerAccount();
        $account->id = 1;
        $account->number_account = $accountNumber;
        $account->balance = $balance;
        $account->updated_at = now();

        $this->customerAccountRepositoryMock->shouldReceive('findByAccountNumber')
            ->once()
            ->with($accountNumber)
            ->andReturn($account);

        $result = $this->customerAccountBo->getBalance($request);

        $this->assertIsArray($result);
        $this->assertEquals($accountNumber, $result['account_number']);
        $this->assertEquals($balance, $result['balance']);
        $this->assertArrayHasKey('last_update', $result);
    }

    public function testGetBalanceWithNonExistentAccount()
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
        $this->customerAccountBo->getBalance($request);
    }
}
