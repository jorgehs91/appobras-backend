<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class CompanyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companies = $user->companies()->orderBy('name')->get();
        return CompanyResource::collection($companies)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $company = Company::query()->create([
            'name' => (string) $validated['name'],
        ]);

        $user->companies()->syncWithoutDetaching([$company->id]);
        // define contexto de equipe
        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        // Admin Obra
        $user->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        // define company ativa
        $user->current_company_id = $company->id;
        $user->save();

        return (new CompanyResource($company))->response()->setStatusCode(201);
    }
}


