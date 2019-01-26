<?php

namespace App\Http\Controllers\Api;

use App\Company;
use App\Helpers\CacheHelper;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CompaniesController extends Controller
{
    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $companies = Cache::rememberForever('companies', function () {
            return Company::all();
        });

        return CompanyResource::collection($companies);
    }

    /**
     * @param Request $request
     * @return CompanyResource|JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'registration_code' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return new JsonResponse($validator->errors(), 400);
        }

        $company = Company::create([
            'name' => $request->input('name'),
            'registration_code' => $request->input('registration_code')
        ]);

        CacheHelper::forgetIfExists(['companies']);

        return new CompanyResource($company);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        $company = Cache::rememberForever('company_' . $id, function () use($id) {
            return Company::with('sellerContracts', 'clientContracts')->find($id);
        });

        if (!$company) {
            return new JsonResponse('Company was not found', 400);
        }

        return $company;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return CompanyResource|JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'registration_code' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return new JsonResponse($validator->errors(), 400);
        }

        $company = Company::find($id);

        if (!$company) {
            return response()->json('Company has not been found', 400);
        }

        $company->update($request->only(['name', 'registration_code']));

        $cacheKeys = [];

        foreach ($company->sellerContracts as $sellerContract) {
            $cacheKeys[] = 'contract_' . $sellerContract->id;
        }

        foreach ($company->clientContracts as $clientContract) {
            $cacheKeys[] = 'contract_' . $clientContract->id;
        }

        CacheHelper::forgetIfExists(array_merge([
            'companies',
            'company_' . $company->id
        ], $cacheKeys));

        return new CompanyResource($company);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json('Company has not been found', 400);
        }

        $cacheKeys = [];

        foreach ($company->sellerContracts as $sellerContract) {
            $cacheKeys[] = 'contract_' . $sellerContract->id;
        }

        foreach ($company->clientContracts as $clientContract) {
            $cacheKeys[] = 'contract_' . $clientContract->id;
        }

        CacheHelper::forgetIfExists(array_merge([
            'companies',
            'company_' . $company->id,
            'contracts'
        ], $cacheKeys));

        Company::destroy($id);

        return response()->json('Company has been deleted', 200);
    }
}
