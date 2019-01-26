<?php

namespace Tests\Feature;

use App\Company;
use App\Contract;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiContractsControllerTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    private function beforeTest() {
        Company::create([
            'id' => 1,
            'name' => 'Company_TEST',
            'registration_code' => '007TEST'
        ]);
        Company::create([
            'id' => 2,
            'name' => 'Company_TEST',
            'registration_code' => '007TEST'
        ]);

        Contract::create([
            'id' => 1,
            'seller_company_id' => 1,
            'client_company_id' => 2,
            'contract_number' => 'Contract_TEST',
            'signed' => '2019-01-01',
            'valid_till' => '2019-02-02',
            'credits' => 10
        ]);
    }

    private function afterTest() {
        Artisan::call('migrate:reset');
    }

    public function testStoreContractSuccess()
    {
        Company::create([
            'id' => 1,
            'name' => 'Company_TEST',
            'registration_code' => '007TEST'
        ]);
        Company::create([
            'id' => 2,
            'name' => 'Company_TEST',
            'registration_code' => '007TEST'
        ]);

        $contractNumber = '111AAA';
        $signed = '2019-02-03';
        $validTill = '2020-02-03';
        $sellerCompanyId = 2;
        $clientCompanyId = 1;
        $credits = 10;

        $response = $this->json('POST', '/api/contracts',
            [
                'seller_company_id' => $sellerCompanyId,
                'client_company_id' => $clientCompanyId,
                'contract_number' => $contractNumber,
                'signed' => $signed,
                'valid_till' => $validTill,
                'credits' => $credits
            ]
        );

        $response->assertStatus(201);
        $response = json_decode($response->content(), 1);

        $response = $response['data'];

        $this->assertEquals($response['seller_company_id'], $sellerCompanyId);
        $this->assertEquals($response['client_company_id'],  $clientCompanyId);
        $this->assertEquals($response['contract_number'],  $contractNumber);
        $this->assertEquals($response['signed'],  $signed);
        $this->assertEquals($response['valid_till'],  $validTill);
        $this->assertEquals($response['credits'],  $credits);
    }

    public function testStoreContractFailRequiredParams()
    {
        $response = $this->json('POST', '/api/contracts', []);
        $response->assertStatus(400);
    }

    public function testUpdateContractSuccess()
    {
        $this->beforeTest();

        $contractNumber = '111AAA';
        $signed = '2019-02-03';

        $response = $this->json('PUT', '/api/contracts/1',
            [
                'contract_number' => $contractNumber,
                'signed' => $signed,
                'seller_company_id' => 2,
                'client_company_id' => 1,
                'valid_till' => '2020-02-02'
            ]
        );

        $response->assertStatus(200);
        $response = json_decode($response->content(), 1);

        $response = $response['data'];

        $this->assertEquals($response['signed'], $signed);
        $this->assertEquals($response['contract_number'],  $contractNumber);
    }

    public function testUpdateContractNotExists()
    {
        $this->beforeTest();

        $response = $this->json('PUT', '/api/contracts/2', []);
        $response->assertStatus(400);
    }

    public function testUpdateContractFailCompanyNotExists()
    {
        $this->beforeTest();

        $response = $this->json('PUT', '/api/contracts/1', ['seller_company_id' => 3]);
        $response->assertStatus(400);
    }

    public function testUpdateContractFailTheSameCompanies()
    {
        $this->beforeTest();

        $response = $this->json('PUT', '/api/contracts/1', [
            'seller_company_id' => 1,
            'client_company_id' => 1
        ]);

        $response->assertStatus(400);

        $response->assertSee("You use the same companies!");
    }

    public function testUpdateContractFailIncorrectDates()
    {
        $this->beforeTest();

        $response = $this->json('PUT', '/api/contracts/1', [
            'valid_till' => '2019-02-02',
            'signed' => '2020-02-02'
        ]);

        $response->assertSee("The end of contract can not be less than the signed date!");

        $response->assertStatus(400);
    }

    public function testShowContractSuccess()
    {
        $this->beforeTest();

        $response = $this->json('GET', '/api/contracts/1', []);
        $response->assertStatus(200);
    }

    public function testShowContractFail()
    {
        $response = $this->json('GET', '/api/contracts/1', []);
        $response->assertStatus(400);
    }

    public function testIndexContractsSuccess()
    {
        $this->beforeTest();

        $response = $this->json('GET', '/api/contracts', []);
        $response->assertStatus(200);
    }

    public function testDestroyCompanySuccess()
    {
       $this->beforeTest();

        $response = $this->json('DELETE', '/api/contracts/1');
        $response->assertStatus(200);
    }

    public function testDestroyCompanyFail()
    {
        $this->beforeTest();
        $response = $this->json('DELETE', '/api/contracts/2');
        $response->assertStatus(400);
    }
}
