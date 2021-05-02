<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Game;
use App\Models\Rule;
use App\Models\DeleteFile;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Request;
use Illuminate\Validation\Rule as RuleHelper;
use App\Hooks\StoringAccountHook;
use App\Hooks\StoredAccountHook;
use App\Hooks\UpdatingAccountHook;
use App\Hooks\UpdatedAccountHook;
use App\Hooks\ApprovingAccountHook;
use App\Hooks\ApprovedAccountHook;
use App\Hooks\BuyingAccountHook;
use App\Hooks\BoughtAccountHook;
use App\Models\GameInfo;
use Carbon\Carbon;

class AccountController extends Controller
{
    private $config = [
        'key' => 'id' # Use as prefix account actions and account infos
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return AccountResource::collection(Account::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountRequest $request, AccountType $accountType)
    {
        $game = $accountType->game;

        // Get role use to create account
        $roleThatUsing = auth()->user()->roles->find($request->roleKey);
        if (is_null($roleThatUsing)) {
            return response()->json([
                'errors' => [
                    'roleKey' => 'Vai trò không hợp lệ.'
                ]
            ], 422);
        }

        // Validate
        {
            // Validate Account infos
            $validate = Validator::make(
                $request->accountInfos ?? [], # case accountInfo is null
                $this->makeRuleAccountInfos($accountType->accountInfos, $roleThatUsing),
            );
            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Thông tin tài khoản không hợp lệ.',
                    'errors' => ['accountInfos' => $validate->errors()],
                ], 422);
            }

            // Validate Account actions
            $validate = Validator::make(
                $request->accountActions ?? [], # case accountInfo is null
                $this->makeRuleAccountActions($accountType->accountActions, $roleThatUsing),
            );
            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Một số hành động bắt buộc đối với tài khoản còn thiếu.',
                    'errors' => ['accountActions' => $validate->errors()],
                ], 422);
            }

            // Validate Game infos
            $validate = Validator::make(
                $request->gameInfos ?? [], # case accountInfo is null
                $this->makeRuleGameInfos($game->gameInfos, $roleThatUsing),
            );
            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Một số thông tin game không hợp lệ.',
                    'errors' => ['gameInfos' => $validate->errors()],
                ], 422);
            }
        }

        // Make data to save
        {
            // Initialize data
            $account = new Account;
            foreach ([
                'username', 'password', 'cost', 'description'
            ] as $key) {
                if ($request->filled($key)) {
                    $snackKey = Str::snake($key);
                    $account->$snackKey = $request->$key;
                }
            }

            // Process other account info
            $account->game_id = $game->getKey();
            $account->account_type_id = $accountType->getKey();
            $account->last_role_key_editor_used = $roleThatUsing->getKey();

            // Process advance account info
            $account->status_code = $this->getStatusCode($accountType, $roleThatUsing);
        }

        try {
            DB::beginTransaction();
            $imagePathsNeedDeleteWhenFail = [];

            // handle representative
            if ($request->hasFile('representativeImage')) {
                $account->representative_image_path
                    = $request->representativeImage->store('public/account-images');
                $imagePathsNeedDeleteWhenFail[] = $account->representative_image_path;
            }

            // Save account in database
            StoringAccountHook::execute($account); #Hook
            $account->save();

            // Handle relationship
            {
                // Account info
                $syncInfos = [];
                foreach ($request->accountInfos ?? [] as $key => $value) {
                    $id = (int)trim($key, $this->config['key']);
                    if ($accountType->accountInfos->contains($id)) {
                        $syncInfos[$id] =  ['value' => json_encode($value)];
                    }
                }
                $account->infos()->sync($syncInfos);

                // Account action
                $syncActions = [];
                foreach ($request->accountActions ?? [] as $key => $value) {
                    $id = (int)trim($key, $this->config['key']);
                    if ($accountType->accountActions->contains($id)) {
                        $syncActions[$id] = ['value' => json_encode($value)];
                    }
                }
                $account->actions()->sync($syncActions);

                // game info
                $syncGameInfos = [];
                foreach ($request->gameInfos ?? [] as $key => $value) {
                    $id = (int)trim($key, $this->config['key']);
                    if ($game->gameInfos->contains($id)) {
                        $syncGameInfos[$id] = ['value' => json_encode($value)];
                    }
                }
                $account->gameInfos()->sync($syncGameInfos);
            }

            // handle sub account images
            if ($request->hasFile('images')) {
                foreach ($request->images as $image) {
                    $imagePath = $image->store('public/account-images');
                    $imagePathsNeedDeleteWhenFail[] = $imagePath;
                    $account->images()->create(['path' => $imagePath]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            // Handle delete images
            foreach ($imagePathsNeedDeleteWhenFail as $imagePath) {
                Storage::delete($imagePath);
            }
            return $th;
            return response()->json([
                'message' => 'Thêm tài khoản vào hệ thống thất bại.'
            ], 500);
        }

        StoredAccountHook::execute($account);
        return new AccountResource($account->refresh());
    }

    /**
     * Approve account to publish account to user buyable.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, Account $account)
    {
        try {
            ApprovingAccountHook::execute($account);

            switch ($account->status_code) {
                case 0:
                    $account->status_code = 480;
                    break;

                default:
                    return response()->json([
                        'message' => 'Account don\'t allow approve.',
                    ], 503);
                    break;
            }

            // When success
            $account->approved_at = Carbon::now();
            $account->save();
            ApprovedAccountHook::execute($account);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Lỗi nội bộ sever, vui lòng thử lại sau.',
            ], 500);
        }

        // Done
        return new AccountResource($account);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        return new AccountResource($account);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountRequest $request, Account $account)
    {
        $accountType = $account->accountType;
        $game = $accountType->game;

        // Get role use to update
        $roleThatUsing = auth()->user()->roles->find($request->roleKey ?? $account->last_role_key_editor_used);
        if (is_null($roleThatUsing)) {
            return response()->json([
                'errors' => [
                    'roleKey' => 'Vai trò không hợp lệ.'
                ]
            ], 422);
        }

        // Validate account info and account action
        {
            // Validate Account infos
            if ($request->accountInfos) {
                $validate = Validator::make(
                    $request->accountInfos ?? [], # case accountInfo is null
                    $this->makeRuleAccountInfos($accountType->accountInfos, $roleThatUsing),
                );
                if ($validate->fails()) {
                    return response()->json([
                        'message' => 'Thông tin tài khoản không hợp lệ.',
                        'errors' => ['accountInfos' => $validate->errors()],
                    ], 422);
                }
            }

            // Validate Account actions
            if ($request->accountActions) {
                $validate = Validator::make(
                    $request->accountActions ?? [], # case accountAction is null
                    $this->makeRuleAccountActions($accountType->accountActions, $roleThatUsing),
                );
                if ($validate->fails()) {
                    return response()->json([
                        'message' => 'Một số hành động bắt buộc đối với tài khoản còn thiếu.',
                        'errors' => ['accountActions' => $validate->errors()],
                    ], 422);
                }
            }

            // Validate Game infos
            if ($request->gameInfos) {
                $validate = Validator::make(
                    $request->gameInfos ?? [], # case accountInfo is null
                    $this->makeRuleGameInfos($game->gameInfos, $roleThatUsing),
                );
                if ($validate->fails()) {
                    return response()->json([
                        'message' => 'Một số thông tin game không hợp lệ.',
                        'errors' => ['gameInfos' => $validate->errors()],
                    ], 422);
                }
            }
        }

        // Make data to save
        {
            // Initialize data
            foreach ([
                'username', 'password', 'cost', 'description'
            ] as $key) {
                if ($request->filled($key)) {
                    $snackKey = Str::snake($key);
                    $account->$snackKey = $request->$key;
                }
            }

            // Process other account info
            $account->last_role_key_editor_used = $roleThatUsing->getKey();
        }


        try {
            DB::beginTransaction();
            $imagePathsNeedDeleteWhenFail = [];
            $imagePathsNeedDeleteWhenSuccess = [];

            // handle representative
            if ($request->hasFile('representativeImage')) {
                $imagePathsNeedDeleteWhenSuccess[]
                    = $account->representative_image_path;
                $account->representative_image_path
                    = $request->representativeImage
                    ->store('public/account-images');
                $imagePathsNeedDeleteWhenFail[]
                    = $account->representative_image_path;
            }

            // Save account in database
            UpdatingAccountHook::execute($account);
            $account->save();

            // Handle relationship
            {
                // account infos
                if ($request->accountInfos) {
                    $syncInfos = [];
                    foreach ($request->accountInfos ?? [] as $key => $value) {
                        $id = (int)trim($key, $this->config['key']);
                        if ($accountType->accountInfos->contains($id)) {
                            $syncInfos[$id] =  ['value' => json_encode($value)];
                        }
                    }
                    $account->infos()->sync($syncInfos);
                }


                // account actions
                if ($request->accountActions) {
                    $syncActions = [];
                    foreach ($request->accountActions ?? [] as $key => $value) {
                        $id = (int)trim($key, $this->config['key']);
                        if ($accountType->accountActions->contains($id)) {
                            $syncActions[$id] = ['value' => json_encode($value)];
                        }
                    }
                    $account->actions()->sync($syncActions);
                }

                // game info
                if ($request->gameInfos) {
                    $syncGameInfos = [];
                    foreach ($request->gameInfos ?? [] as $key => $value) {
                        $id = (int)trim($key, $this->config['key']);
                        if ($game->gameInfos->contains($id)) {
                            $syncGameInfos[$id] = ['value' => json_encode($value)];
                        }
                    }
                    $account->gameInfos()->sync($syncGameInfos);
                }

                // sub account images
                if ($request->hasFile('images')) {
                    foreach ($request->images as $image) {
                        $imagePath = $image->store('public/account-images');
                        $imagePathsNeedDeleteWhenFail[] = $imagePath;
                        $account->images()->create(['path' => $imagePath]);
                    }
                }
            }

            // When success
            foreach ($imagePathsNeedDeleteWhenSuccess as $imagePath) {
                Storage::delete($imagePath);
            }
            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            // Handle delete images
            foreach ($imagePathsNeedDeleteWhenFail as $imagePath) {
                Storage::delete($imagePath);
            }
            return $th;
            return response()->json([
                'message' => 'Chỉnh sửa tài khoản vào hệ thống thất bại.'
            ], 500);
        }

        UpdatedAccountHook::execute($account);
        return new AccountResource($account);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        return false; // don't allow destroy account

        // DB transaction
        try {
            DB::beginTransaction();
            $imagePathsNeedDeleteWhenSuccess = [];

            // Get image must delete
            $imagePathsNeedDeleteWhenSuccess[] = $account->representative_image_path;
            foreach ($account->images as $image) {
                $imagePathsNeedDeleteWhenSuccess[] = $image->path;
            }

            $account->images()->delete(); // Delete account images
            $account->delete(); // Delete account

            // When success
            foreach ($imagePathsNeedDeleteWhenSuccess as $imagePath) {
                Storage::delete($imagePath);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Xoá tài khoản thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return response()->json([
            'message' => 'Xoá tài khoản thành công.',
        ], 200);
    }

    // -------------------------------------------------------
    // -------------------------------------------------------
    // -------------------------------------------------------
    // -------------------------------------------------------

    private function makeRuleAccountInfos($accountInfos, Role $role)
    {
        // Initial data
        $rules = [];
        foreach ($accountInfos as $accountInfo) {
            // Get rule
            $rule = $accountInfo->rule->generateRule($role);

            // Make rule for validate
            if ($rule['nested']) { # if nested
                $rules[$this->config['key'] . $accountInfo->id] = $rule['ruleOfParent'];
                $rules[$this->config['key'] . $accountInfo->id . '.*'] = $rule['ruleOfChild'];
            } else {
                $rules[$this->config['key'] . $accountInfo->id] = $rule['rule'];
            }
        }

        return $rules;
    }

    private function makeRuleAccountActions($accountActions, Role $role)
    {

        // Initial data
        $rules = [];
        foreach ($accountActions as $accountAction) {
            // Make rule
            $rule = $accountAction->generateRule($role);
            $rules[$this->config['key'] . $accountAction->id] = $rule;
        }

        return $rules;
    }

    private function makeRuleGameInfos($gameInfos, Role $role)
    {
        // Initial data
        $rules = [];
        foreach ($gameInfos as $gameInfo) {
            // Get rule
            $rule = $gameInfo->rule->generateRule($role);

            // Make rule for validate
            if ($rule['nested']) { # if nested
                $rules[$this->config['key'] . $gameInfo->getKey()] = $rule['ruleOfParent'];
                $rules[$this->config['key'] . $gameInfo->getKey() . '.*'] = $rule['ruleOfChild'];
            } else {
                $rules[$this->config['key'] . $gameInfo->getKey()] = $rule['rule'];
            }
        }

        return $rules;
    }

    private function getStatusCode(AccountType $accountType, Role $role)
    {
        return $accountType->rolesCanUsedAccountType
            ->find($role->getKey())
            ->pivot->status_code;
    }
}
