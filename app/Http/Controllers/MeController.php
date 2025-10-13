<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function switchCompany(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_unless($user->companies()->whereKey($validated['company_id'])->exists(), 403);

        $user->current_company_id = (int) $validated['company_id'];
        $user->save();

        return response()->json(['message' => 'Company switched'], 200);
    }
}


