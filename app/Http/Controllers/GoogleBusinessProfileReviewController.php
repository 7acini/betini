<?php

namespace App\Http\Controllers;

use App\Services\GoogleBusinessProfileReviewService;
use Illuminate\Http\JsonResponse;

class GoogleBusinessProfileReviewController extends Controller
{
    public function __invoke(GoogleBusinessProfileReviewService $reviews): JsonResponse
    {
        return response()->json($reviews->reviews());
    }
}
