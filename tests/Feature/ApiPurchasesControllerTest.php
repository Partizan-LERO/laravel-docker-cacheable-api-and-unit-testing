<?php

namespace Tests\Feature;

use App\Company;
use App\Contract;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiPurchasesControllerTest extends TestCase
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

    public function testStorePurchaseSuccess()
    {
        $this->beforeTest();

        $contractId = 1;
        $creditsSpent = 9;
        $datetime = '2019-01-05 20:20:20';
        $response = $this->json('POST', '/api/purchases',
            [
                'contract_id' => $contractId,
                'credits_spent' => $creditsSpent,
                'datetime' => $datetime
            ]
        );

        $this->assertDatabaseHas('purchases', [
            'contract_id' => $contractId,
            'credits_spent' => $creditsSpent,
            'datetime' => $datetime
        ]);

        $response->assertStatus(201);
        $response = json_decode($response->content(), 1);

        $response = $response['data'];
        $this->assertArrayHasKey('contract_id', $response);
        $this->assertArrayHasKey('credits_spent', $response);
        $this->assertArrayHasKey('datetime', $response);

        $this->assertEquals($response['contract_id'], $contractId);
        $this->assertEquals($response['datetime'],  $datetime);
        $this->assertEquals($response['credits_spent'],  $creditsSpent);
    }

    public function testStorePurchaseFailRequiredParams()
    {
        $this->beforeTest();
        $response = $this->json('POST', '/api/purchases', []);
        $response->assertStatus(400);
    }

    public function testStorePurchaseFailFormatDateTime()
    {
        $this->beforeTest();
        $response = $this->json('POST', '/api/purchases', [
            'datetime' => '2019-02-03'
        ]);
        $response->assertStatus(400);
        $response = json_decode($response->content(), 1);

        $this->assertEquals($response['datetime'][0], "The datetime does not match the format Y-m-d H:i:s.");

    }

    public function testStorePurchaseFailDateTime()
    {
        $this->beforeTest();
        $response = $this->json('POST', '/api/purchases', [
            'datetime' => '2019-02-04 20:20:20',
            'contract_id' => 1,
            'credits_spent' => 9
        ]);

        $response->assertStatus(400);
        $response = json_decode($response->content(), 1);

        $this->assertEquals($response, "The date of operation out of contract duration");
    }

    public function testStorePurchaseFailCredits()
    {
        $this->beforeTest();
        $response = $this->json('POST', '/api/purchases', [
            'datetime' => '2019-02-01 20:20:20',
            'contract_id' => 1,
            'credits_spent' => 11
        ]);

        $response->assertStatus(400);
        $response = json_decode($response->content(), 1);

        $this->assertEquals($response, "There are not enough credits for this operation");
    }

}
