<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Http\Requests\StoreAccountTypeRequest;
use App\Http\Requests\UpdateAccountTypeRequest;
use App\Http\Resources\AccountTypeResource;
use App\Models\Publisher;
use Str;
use Auth;
use DB;

class AccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return AccountTypeResource::collection(AccountType::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountTypeRequest $request)
    {
        // Get publisher
        $publisher = Publisher::find($request->publisherId);
        if (is_null($publisher)) {
            return response()->json([
                'message' => 'ID nhà phát hành không tồn tại.',
            ], 404);
        }

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
        $accountTypeData['publisher_id'] = $publisher->id;
        $accountTypeData['last_updated_editor_id'] = Auth::user()->id;
        $accountTypeData['creator_id'] = Auth::user()->id;

        // DB transaction
        try {
            DB::beginTransaction();
            $accountType = AccountType::create($accountTypeData); // Save rule to database

            // Relationship many-many with Models\Role
            $role = Role::all();
            $syncRoleIds = [];
            foreach ($request->roleIds ?? [] as $roleId) {
                if ($role->contains($roleId)) {
                    $syncRoleIds[] = $roleId;
                }
            }
            $accountType->roles()->sync($syncRoleIds);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Thêm mới kiểu tài khoản thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return new AccountTypeResource($accountType->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function show(AccountType $accountType)
    {
        return new AccountTypeResource($accountType);
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
        $accountTypeData['last_updated_editor_id'] = Auth::user()->id;

        // DB transaction
        try {
            DB::beginTransaction();
            $accountType->update($accountTypeData); // Save rule to database

            // Relationship many-many with Models\Role
            $role = Role::all();
            $syncRoleIds = [];
            foreach ($request->roleIds ?? [] as $roleId) {
                if ($role->contains($roleId)) {
                    $syncRoleIds[] = $roleId;
                }
            }
            $accountType->roles()->sync($syncRoleIds);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Cập nhật kiểu tài khoản thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return new AccountTypeResource($accountType);
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
            $accountType->roles()->sync([]); // Delete relationship with Models\Role
            $accountType->delete(); // Update publisher to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Xoá kiểu tài khoản thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return response()->json([
            'message' => 'Xoá kiểu tài khoản thành công.',
        ], 200);
    }
}
