<?php

namespace Tests\Feature;


use App\Company;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiCompaniesControllerTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    public function testStoreCompanySuccess()
    {
        $name = 'TEST_COMPANY';
        $registrationCode = 'AAA121';
        $response = $this->json('POST', '/api/companies',
            [
                'name' => $name,
                'registration_code' => $registrationCode
            ]
        );

        $this->assertDatabaseHas('companies', [
            'name' => $name,
            'registration_code' => $registrationCode
        ]);

        $response->assertStatus(201);
        $response = json_decode($response->content(), 1);

        $response = $response['data'];
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('registration_code', $response);

        $this->assertEquals($response['name'], $name);
        $this->assertEquals($response['registration_code'],  $registrationCode);
    }

    public function testStoreCompanyFailRequiredParams()
    {
        $response = $this->json('POST', '/api/companies', []);
        $response->assertStatus(400);
    }

    public function testUpdateCompanySuccess()
    {
        Company::create([
            'id' => 1,
            'name' => 'Company_TEST',
            'registration_code' => '007TEST'
        ]);

        $name = 'TEST_COMPANY';
        $registrationCode = 'AAA121';

        $response = $this->json('PUT', '/api/companies/1',
            [
                'name' => $name,
                'registration_code' => $registrationCode
            ]
        );

        $response->assertStatus(200);
        $response = json_decode($response->content(), 1);

        $response = $response['data'];
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('registration_code', $response);

        $this->assertEquals($response['name'], $name);
        $this->assertEquals($response['registration_code'],  $registrationCode);
    }

    public function testUpdateCompanyNotExists()
    {
        $response = $this->json('PUT', '/api/companies/1', []);
        $response->assertStatus(400);
    }

    public function testShowCompanySuccess()
    {
        Company::create([
            'id' => 1,
            'name' => 'Company_TEST',
            'registration_code' => '007TEST'
        ]);

        $response = $this->json('GET', '/api/companies/1', []);
        $response->assertStatus(200);
    }

    public function testShowCompanyFail()
    {
        $response = $this->json('GET', '/api/companies/1', []);
        $response->assertStatus(400);
    }

    public function testIndexCompanySuccess()
    {
        Company::create([
            'id' => 1,
            'name' => 'Company_TEST',
            'registration_code' => '007TEST'
        ]);

        $response = $this->json('GET', '/api/companies', []);
        $response->assertStatus(200);
    }

    public function testDestroyCompanySuccess()
    {
        Company::create([
            'id' => 1,
            'name' => 'Company_TEST',
            'registration_code' => '007TEST'
        ]);

        $response = $this->json('DELETE', '/api/companies/1');
        $response->assertStatus(200);
    }

    public function testDestroyCompanyFail()
    {
        Company::create([
            'id' => 1,
            'name' => 'Company_TEST',
            'registration_code' => '007TEST'
        ]);

        $response = $this->json('DELETE', '/api/companies/2');
        $response->assertStatus(400);
    }
}
