<?php

namespace App\Http\Controllers;

use App\Models\AccountAction;
use Illuminate\Http\Request;
use App\Http\Resources\AccountActionResource;
use DB;
use Illuminate\Support\Str;
use App\Http\Requests\StoreAccountActionRequest;
use App\Http\Requests\UpdateAccountActionRequest;
use App\Models\AccountType;

class AccountActionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return AccountActionResource::collection(AccountAction::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountActionRequest $request, AccountType $accountType)
    {
        // Initialize data
        $accountActionData = [];
        foreach ([
            'order', 'name', 'description', 'videoPath', 'required'
        ] as $key) {
            if ($request->filled($key)) {
                $accountActionData[$key] = $request->$key;
            }
        }
        $accountActionData['slug'] = Str::slug($accountActionData['name']);
        $accountActionData['account_type_id'] = $accountType->id;
        $accountActionData['last_updated_editor_id'] = auth()->user()->id;
        $accountActionData['creator_id'] = auth()->user()->id;

        // DB transaction
        try {
            DB::beginTransaction();
            $accountAction = AccountAction::create($accountActionData); // Save account info to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Thêm mới công việc cần thiết để đăng tài khoản thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return new AccountActionResource($accountAction->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountAction  $accountAction
     * @return \Illuminate\Http\Response
     */
    public function show(AccountAction $accountAction)
    {
        return new AccountActionResource($accountAction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountAction  $accountAction
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountActionRequest $request, AccountAction $accountAction)
    {
        // Initialize data
        $accountActionData = [];
        foreach ([
            'order', 'name', 'description', 'videoPath', 'required'
        ] as $key) {
            if ($request->filled($key)) {
                $accountActionData[Str::snake($key)] = $request->$key;
            }
        }
        if (array_key_exists('name', $accountActionData)) {
            $accountActionData['slug'] = Str::slug($accountActionData['name']);
        }
        $accountActionData['last_updated_editor_id'] = auth()->user()->id;

        // DB transaction
        try {
            DB::beginTransaction();
            $accountAction->update($accountActionData); // Save account info to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Cập nhật công việc cần thiết để đăng tài khoản thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return new AccountActionResource($accountAction->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountAction  $accountAction
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountAction $accountAction)
    {
        // DB transaction
        try {
            DB::beginTransaction();
            $accountAction->delete(); // Update publisher to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Xoá công việc cần thiết để đăng tài khoản thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return response()->json([
            'message' => 'Xoá công việc cần thiết để đăng tài khoản thành công.',
        ], 200);
    }
}
