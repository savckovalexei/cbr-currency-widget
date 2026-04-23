<?php

namespace App\Http\Controllers\Api;

use App\Actions\Rates\GetRatesForWidgetAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RateIndexRequest;

class RateController extends Controller
{
    public function __construct(
        private readonly GetRatesForWidgetAction $getRatesAction
    ) {}

    public function index(RateIndexRequest $request)
    {
        $date = $request->input('date');
        $rates = $this->getRatesAction->execute($date);

        return response()->json($rates);
    }
}