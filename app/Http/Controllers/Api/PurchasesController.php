<?php

namespace App\Http\Controllers\Api;

use App\Contract;
use App\Helpers\CacheHelper;
use App\Http\Resources\PurchaseResource;
use App\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PurchasesController extends Controller
{
    /**
     * @param Request $request
     * @return PurchaseResource|JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contract_id' => 'required|exists:contracts,id',
            'datetime' => 'required|date|date_format:Y-m-d H:i:s',
            'credits_spent' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return new JsonResponse($validator->errors(), 400);
        }

        $contractId = $request->input('contract_id');
        $credits = $request->input('credits_spent');
        $datetime = $request->input('datetime');

        $contract = Contract::find($contractId);

        $operationDate = new \DateTime($datetime);

        if ($contract->valid_till < $operationDate->format('Y-m-d')) {
            return new JsonResponse("The date of operation out of contract duration", 400);
        }

        $creditsLeft = $this->getCreditsLeft($contract);

        if ($creditsLeft < $credits) {
            return new JsonResponse("There are not enough credits for this operation", 400);
        }

        $purchase = Purchase::create([
            'contract_id' => $contractId,
            'datetime' => $datetime,
            'credits_spent' => $credits
        ]);

        CacheHelper::forgetIfExists(['contract_' . $request->input('contract_id')]);

        return new PurchaseResource($purchase);
    }


    /**
     * @param Contract $contract
     * @return int|mixed
     */
    public function getCreditsLeft(Contract $contract) {
        $creditsTotal = $contract->credits;
        $creditsSpentTotal = 0;

        $purchases = Purchase::where('contract_id', $contract->id)->get();

        if (count($purchases) > 0) {
            foreach ($purchases as $purchase) {
                $creditsSpentTotal += $purchase->credits_spent;
            }
        }

        return $creditsTotal - $creditsSpentTotal;
    }
}
