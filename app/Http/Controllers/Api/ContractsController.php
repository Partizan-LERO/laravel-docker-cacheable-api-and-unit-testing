<?php

namespace App\Http\Controllers\Api;

use App\Contract;
use App\Helpers\CacheHelper;
use App\Http\Resources\ContractResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class ContractsController extends Controller
{
    public $perPage = 2;

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);

        $contracts = Contract::paginate($this->perPage);
        Cache::tags('contracts')->put('contracts_' . 'pp_' . $this->perPage. '_page_' . $page,  $contracts, 60);

        return ContractResource::collection($contracts);
    }

    /**
     * @param Request $request
     * @return ContractResource|JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seller_company_id' => 'required|exists:companies,id',
            'client_company_id' => 'required|exists:companies,id',
            'contract_number' => 'required|string|max:255',
            'valid_till' => 'required|date|date_format:Y-m-d',
            'signed' => 'required|date|date_format:Y-m-d',
            'credits' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return new JsonResponse($validator->errors(), 400);
        }

        $sellerCompanyId = $request->input('seller_company_id');
        $clientCompanyId = $request->input('client_company_id');

        if ($sellerCompanyId == $clientCompanyId) {
            return new JsonResponse("You use the same companies!", 400);
        }

        $validTill = $request->input('valid_till');
        $signed = $request->input('signed');

        if ($signed > $validTill) {
            return new JsonResponse("The end of contract can't be less than the signed date!", 400);
        }

        $contract = Contract::create([
            'seller_company_id' => $sellerCompanyId,
            'client_company_id' => $clientCompanyId,
            'contract_number' => $request->input('contract_number'),
            'valid_till' => $request->input('valid_till'),
            'signed' => $request->input('signed'),
            'credits' => $request->input('credits'),
        ]);

        Cache::tags('contracts')->flush();

        CacheHelper::forgetIfExists([
            'company_' . $sellerCompanyId,
            'company_' . $clientCompanyId
        ]);

        return new ContractResource($contract);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        $contract = Cache::rememberForever('contract_' . $id, function () use($id) {
            return Contract::with('sellerCompany', 'clientCompany', 'purchases')->find($id);
        });

        if (!$contract) {
            return new JsonResponse('Contract has not been found', 400);
        }

        return $contract;
    }

    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'seller_company_id' => 'exists:companies,id',
            'client_company_id' => 'exists:companies,id',
            'contract_number' => 'string|max:255',
            'valid_till' => 'date|date_format:Y-m-d',
            'signed' => 'date|date_format:Y-m-d',
            'credits' => 'integer',
        ]);

        if ($validator->fails()) {
            return new JsonResponse($validator->errors(), 400);
        }

        $sellerCompanyId = $request->input('seller_company_id');
        $clientCompanyId = $request->input('client_company_id');

        if (!is_null($sellerCompanyId) && !is_null($clientCompanyId)) {
            if ($sellerCompanyId == $clientCompanyId) {
                return new JsonResponse("You use the same companies!", 400);
            }
        }

        $validTill = $request->input('valid_till');
        $signed = $request->input('signed');

        if ($signed > $validTill) {
            return new JsonResponse("The end of contract can not be less than the signed date!", 400);
        }

        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json('Contract has not been found', 400);
        }

        $contract->update($request->only([
            'seller_company_id',
            'client_company_id',
            'contract_number',
            'valid_till',
            'signed',
            'credits'
        ]));

        Cache::tags('contracts')->flush();

        CacheHelper::forgetIfExists([
            'company_' . $contract->seller_company_id,
            'company_' . $contract->client_company_id,
            'contract_' . $contract->id,
            'company_' . $sellerCompanyId,
            'company_' . $clientCompanyId
        ]);

        return new ContractResource($contract);
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json('Contract has not been found', 400);
        }

        Cache::tags('contracts')->flush();

        CacheHelper::forgetIfExists([
            'company_' . $contract->seller_company_id,
            'company_' . $contract->client_company_id,
            'contract_' . $contract->id
        ]);

        Contract::destroy($id);

        return response()->json('Contract has been deleted', 200);
    }
}
