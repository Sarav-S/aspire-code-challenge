<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanRequest;
use App\Models\Loan;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{

    public function index(): JsonResponse
    {
        $loans = Loan::forIndividual(auth()->id());
        return response()->json(['data' => $loans]);
    }

    public function store(LoanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $user->loans()->create($data);
        return response()->json(
            ['message' => 'Loan submitted successfully for admin review'],
            201
        );
    }

    public function show(Request $request, $id): JsonResponse
    {
        $loan = Loan::withTerm($id, auth()->id());
        if (!$loan) {
            return response()->json(['message' => 'Loan not found'], 404);
        }

        return response()->json(['data' => $loan]);
    }
}
