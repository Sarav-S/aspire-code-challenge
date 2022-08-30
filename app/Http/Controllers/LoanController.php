<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoanRequest;

class LoanController extends Controller
{

    public function index() : JsonResponse
    {
        $loans = Loan::forIndividual(auth()->id());
        return response()->json(['data' => $loans]);
    }

    public function store(LoanRequest $request) : JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        DB::beginTransaction();
        try {
            $user->loan()->create($data);
            DB::commit();

            return response()->json(
                ['message' => 'Loan submitted successfully for review'],
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                ['message' => $e->getMessage()],
                400
            );
        }
    }

    public function show(Request $request, $id) : JsonResponse
    {
        $loan = Loan::withTerm($id, auth()->id());
        if (!$loan) {
            return response()->json(['message' => 'Loan not found'], 404);
        }

        return response()->json(['data' => $loan]);
    }
}
