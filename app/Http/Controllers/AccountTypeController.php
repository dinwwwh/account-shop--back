<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Models\AccountType;
use App\Models\Role;
use App\Http\Requests\StoreAccountTypeRequest;
use App\Http\Requests\UpdateAccountTypeRequest;
use App\Http\Resources\AccountTypeResource;
use App\Models\Game;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $_with = $this->requiredModelRelationships;
        $accountTypes = AccountType::with($_with)->paginate(15);
        return AccountTypeResource::collection($accountTypes);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function calculateFee(Request $request, AccountType  $accountType)
    {
        $request->validate([
            'cost' => 'required|integer',
        ]);

        return response([
            'data' => [
                'result' => $accountType->calculateFee($request->cost),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountTypeRequest $request, Game $game)
    {
        // Initialize data
        $accountTypeData = [];
        foreach ([
            'name', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $accountTypeData[$key] = $request->$key;
            }
        }
        $accountTypeData['slug'] = Str::slug($accountTypeData['name']);
        $accountTypeData['game_id'] = $game->id;

        // DB transaction
        try {
            DB::beginTransaction();
            $accountType = AccountType::create($accountTypeData); // Save rule to database

            $syncUsableUsers = [];
            foreach ($request->usableUsers ?? [] as $user) {
                $syncUsableUsers[$user['id']] = [
                    'status_code' => $user['statusCode'],
                ];
            }
            $accountType->usableUsers()->sync($syncUsableUsers);

            $syncApprovableUsers = [];
            foreach ($request->approvableUsers ?? [] as $user) {
                $syncApprovableUsers[$user['id']] = [
                    'status_code' => $user['statusCode'],
                ];
            }
            $accountType->approvableUsers()->sync($syncApprovableUsers);


            // When success
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return AccountTypeResource::withLoadRelationships($accountType);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function show(AccountType $accountType)
    {
        return AccountTypeResource::withLoadRelationships($accountType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountTypeRequest $request, AccountType $accountType)
    {
        // Initialize data
        $accountTypeData = [];
        foreach ([
            'name', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $accountTypeData[$key] = $request->$key;
            }
        }
        if (array_key_exists('name', $accountTypeData)) {
            $accountTypeData['slug'] = Str::slug($accountTypeData['name']);
        }
        $accountTypeData['latest_updater_id'] = Auth::user()->id;

        // DB transaction
        try {
            DB::beginTransaction();
            $accountType->update($accountTypeData); // Save rule to database

            $syncUsableUsers = [];
            foreach ($request->usableUsers ?? [] as $user) {
                $syncUsableUsers[$user['id']] = [
                    'status_code' => $user['statusCode'],
                ];
            }
            $accountType->usableUsers()->sync($syncUsableUsers);

            $syncApprovableUsers = [];
            foreach ($request->approvableUsers ?? [] as $user) {
                $syncApprovableUsers[$user['id']] = [
                    'status_code' => $user['statusCode'],
                ];
            }
            $accountType->approvableUsers()->sync($syncApprovableUsers);
            // When success
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return AccountTypeResource::withLoadRelationships($accountType);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountType $accountType)
    {
        // DB transaction
        try {
            DB::beginTransaction();
            $accountType->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return response()->json([
            'message' => 'Xoá kiểu tài khoản thành công.',
        ], 200);
    }

    // -----------------------------------------------------------
    // -----------------------------------------------------------
    // -----------------------------------------------------------
}
