<?php

namespace App\Http\Controllers;

use App\Models\AccountInfo;
use App\Models\AccountType;
use App\Models\Rule;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Resources\AccountInfoResource;
use App\Http\Requests\StoreAccountInfoRequest;
use App\Http\Requests\UpdateAccountInfoRequest;
use Illuminate\Http\Request;

class AccountInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $_with = $this->requiredModelRelationships;
        $accountInfos = AccountInfo::with($_with)->paginate(15);
        return AccountInfoResource::collection($accountInfos);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountInfoRequest $request, AccountType $accountType)
    {
        // Initialize data
        $accountInfoData = [];
        foreach ([
            'order', 'name', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $accountInfoData[$key] = $request->$key;
            }
        }
        $accountInfoData['slug'] = Str::slug($accountInfoData['name']);
        $accountInfoData['account_type_id'] = $accountType->id;
        $accountInfoData['latest_updater_id'] = auth()->user()->id;
        $accountInfoData['creator_id'] = auth()->user()->id;

        // DB transaction
        try {
            DB::beginTransaction();

            $rule = Rule::create($request->rule ?? [])->refresh(); // Save rule in database
            if (is_null($rule->required)) {
                $requiredRoles = Role::mustBeManyRoles($request->rule['requiredRoleKeys'] ?? []);
                $rule->requiredRoles()->attach($requiredRoles);
            }
            $accountInfoData['rule_id'] = $rule->getKey();
            $accountInfo = AccountInfo::create($accountInfoData); // Save account info to database

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return AccountInfoResource::withLoadRelationships($accountInfo->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountInfo  $accountInfo
     * @return \Illuminate\Http\Response
     */
    public function show(AccountInfo $accountInfo)
    {
        return AccountInfoResource::withLoadRelationships($accountInfo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountInfo  $accountInfo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountInfoRequest $request, AccountInfo $accountInfo)
    {
        // Initialize data
        $accountInfoData = [];
        foreach ([
            'order', 'name', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $accountInfoData[$key] = $request->$key;
            }
        }
        if (array_key_exists('name', $accountInfoData)) {
            $accountInfoData['slug'] = Str::slug($accountInfoData['name']);
        }
        $accountInfoData['latest_updater_id'] = auth()->user()->id;

        // DB transaction
        try {
            DB::beginTransaction();
            $accountInfo->update($accountInfoData);

            // Update rule
            if ($request->filled('rule')) {
                $accountInfo->rule->update($request->rule);
                if (is_null($accountInfo->rule->required)) {
                    $requiredRoles = Role::mustBeManyRoles($request->rule['requiredRoleKeys'] ?? []);
                    $accountInfo->rule->requiredRoles()->sync($requiredRoles);
                } else {
                    $accountInfo->rule->requiredRoles()->sync([]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return AccountInfoResource::withLoadRelationships($accountInfo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountInfo  $accountInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountInfo $accountInfo)
    {
        // DB transaction
        try {
            DB::beginTransaction();
            $accountInfo->rolesNeedFilling()->sync([]); // Delete relationship with Models\Role
            $accountInfo->delete();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return response()->json([
            'message' => 'Xoá thông tin tài khoản cần thiết thành công.',
        ], 200);
    }
}
