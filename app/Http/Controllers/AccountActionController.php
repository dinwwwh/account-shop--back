<?php

namespace App\Http\Controllers;

use App\Models\AccountAction;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Resources\AccountActionResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Requests\StoreAccountActionRequest;
use App\Http\Requests\UpdateAccountActionRequest;
use App\Models\AccountType;
use App\Models\Rule;

class AccountActionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $_with = $this->requiredModelRelationships;
        $accountActions = AccountAction::with($_with)->paginate(15);
        return AccountActionResource::collection($accountActions);
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
            'order', 'name', 'description', 'videoPath'
        ] as $key) {
            if ($request->filled($key)) {
                $accountActionData[Str::snake($key)] = $request->$key;
            }
        }
        $accountActionData['slug'] = Str::slug($accountActionData['name']);
        $accountActionData['account_type_id'] = $accountType->getKey();

        // DB transaction
        try {
            DB::beginTransaction();

            $dataOfRule = $request->rule ?? [];
            $dataOfRule['datatype'] = 'boolean';
            if ($request->rule && array_key_exists('required', $request->rule) && $request->rule['required']) {
                $dataOfRule['values'] = [true];
            }
            $rule = Rule::createQuickly($dataOfRule);
            $accountActionData['rule_id'] = $rule->getKey();

            $accountAction = AccountAction::create($accountActionData)->refresh(); // Save account info to database

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return AccountActionResource::withLoadRelationships($accountAction->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountAction  $accountAction
     * @return \Illuminate\Http\Response
     */
    public function show(AccountAction $accountAction)
    {
        return AccountActionResource::withLoadRelationships($accountAction);
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
            'order', 'name', 'description', 'videoPath'
        ] as $key) {
            if ($request->filled($key)) {
                $accountActionData[Str::snake($key)] = $request->$key;
            }
        }
        if (array_key_exists('name', $accountActionData)) {
            $accountActionData['slug'] = Str::slug($accountActionData['name']);
        }

        // DB transaction
        try {
            DB::beginTransaction();
            $accountAction->update($accountActionData); // Save account info to database

            $dataOfRule = $request->rule ?? [];
            $dataOfRule['datatype'] = 'boolean';
            if ($request->rule && array_key_exists('required', $request->rule) && $request->rule['required']) {
                $dataOfRule['values'] = [true];
            }
            $accountAction->rule->updateQuickly($dataOfRule);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return AccountActionResource::withLoadRelationships($accountAction);
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
            $accountAction->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return response()->json([
            'message' => 'Xoá công việc cần thiết để đăng tài khoản thành công.',
        ], 200);
    }
}
