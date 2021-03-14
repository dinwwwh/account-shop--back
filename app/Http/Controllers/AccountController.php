<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Game;
use App\Models\Rule;
use App\Models\DeleteFile;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use Validator;
use Str;
use DB;
use Storage;
use App\Http\Requests\Request;
use Illuminate\Validation\Rule as RuleHelper;

class AccountController extends Controller
{
    private $config = [
        'key' => 'id' #use prefix account actions and account infos
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
    public function store(StoreAccountRequest $request)
    {
        // dd($request->accountInfos[4]);
        $game = Game::find($request->gameId);
        if (is_null($game)) {
            return response()->json([
                'message' => 'ID game không hợp lệ.'
            ], 404);
        }

        $accountType = $game->currentRoleCanUsedAccountTypes()->find($request->accountTypeId);
        if (is_null($accountType)) {
            return response()->json([
                'message' => 'ID kiểu tài khoản không hợp lệ.'
            ], 404);
        }

        // Validate Account infos
        $validate = Validator::make(
            $request->accountInfos ?? [], # case accountInfo is null
            $this->makeRuleAccountInfos($accountType->currentRoleNeedFillingAccountInfos()),
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
            $this->makeRuleAccountActions($accountType->currentRoleNeedPerformingAccountActions()),
        );
        if ($validate->fails()) {
            return response()->json([
                'message' => 'Một số hành động bắt buộc đối với tài khoản còn thiếu.',
                'errors' => ['accountActions' => $validate->errors()],
            ], 422);
        }


        // Initialize data
        $accountData = [];
        foreach ([
            'username', 'password', 'price', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $accountData[Str::snake($key)] = $request->$key;
            }
        }

        // Process other account info
        $accountData['game_id'] = $game->id;
        $accountData['account_type_id'] = $accountType->id;

        // Process advance account info
        $accountData['status'] = 0;

        try {
            DB::beginTransaction();
            $imagePathsNeedDeleteWhenFail = [];

            // handle representative
            if ($request->hasFile('representativeImage')) {
                $accountData['representative_image_path']
                    = $request->representativeImage->store('public/account-images');
                $imagePathsNeedDeleteWhenFail[] = $accountData['representative_image_path'];
            }

            // Save account in database
            $account = Account::create($accountData);

            // Pivot account infos
            $syncInfos = [];
            foreach ($request->accountInfos ?? [] as $key => $value) {
                $id = (int)trim($key, $this->config['key']);
                if ($accountType->currentRoleNeedFillingAccountInfos()->contains($id)) {
                    $syncInfos[$id] =  ['value' => json_encode($value)];
                }
            }
            $account->infos()->sync($syncInfos);


            // Pivot account actions
            $syncActions = [];
            foreach ($request->accountActions ?? [] as $key => $value) {
                $id = (int)trim($key, $this->config['key']);
                if ($accountType->currentRoleNeedPerformingAccountActions()->contains($id)) {
                    $syncActions[$id] = ['value' => json_encode($value)];
                }
            }
            $account->actions()->sync($syncActions);

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

        return new AccountResource($account->refresh());
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

        // Initialize data
        $accountData = [];
        foreach ([
            'username', 'password', 'price', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $accountData[Str::snake($key)] = $request->$key;
            }
        }

        // Process other account info
        $accountData['last_updated_editor_id'] = auth()->user()->id;

        try {
            DB::beginTransaction();
            $imagePathsNeedDeleteWhenFail = [];
            $imagePathsNeedDeleteWhenSuccess = [];

            // handle representative
            if ($request->hasFile('representativeImage')) {
                $imagePathsNeedDeleteWhenSuccess[]
                    = $account->representative_image_path;
                $accountData['representative_image_path']
                    = $request->representativeImage
                    ->store('public/account-images');
                $imagePathsNeedDeleteWhenFail[]
                    = $accountData['representative_image_path'];
            }

            // Save account in database
            $account->update($accountData);

            // handle sub account images
            if ($request->hasFile('images')) {
                foreach ($request->images as $image) {
                    $imagePath = $image->store('public/account-images');
                    $imagePathsNeedDeleteWhenFail[] = $imagePath;
                    $account->images()->create(['path' => $imagePath]);
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

    private function makeRuleAccountInfos($accountInfos)
    {
        // Initial data
        $rules = [];
        foreach ($accountInfos as $accountInfo) {
            // Get rule
            $rule = $accountInfo->rule->make();

            // Make rule for validate
            if (is_array($rule)) { # If account info is a array
                $rules[$this->config['key'] . $accountInfo->id] = $rule['parent'];
                $rules[$this->config['key'] . $accountInfo->id . '.*'] = $rule['children'];
            } else {
                $rules[$this->config['key'] . $accountInfo->id] = $rule;
            }
        }

        return $rules;
    }

    private function makeRuleAccountActions($accountActions)
    {

        // Initial data
        $rules = [];
        foreach ($accountActions as $accountAction) {
            // Make rule
            $rule = $accountAction->required
                ? 'required|' . RuleHelper::in(true)
                : 'nullable|boolean';
            $rules[$this->config['key'] . $accountAction->id] = $rule;
        }

        return $rules;
    }
}
