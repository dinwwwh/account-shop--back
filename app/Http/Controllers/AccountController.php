<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Http\Requests\Account\UpdateAccountInfosRequest;
use App\Http\Requests\Account\UpdateCostRequest;
use App\Http\Requests\Account\UpdateGameInfosRequest;
use App\Http\Requests\Account\UpdateImagesRequest;
use App\Http\Requests\Account\UpdateLoginInfosRequest;
use App\Models\Account;
use App\Models\AccountType;
use App\Http\Resources\AccountResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Request;
use App\Http\Requests\Account\StoreRequest;
use App\Models\AccountStatus;
use App\Models\File;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $_with = $this->requiredModelRelationships;
        $accounts = Account::with($_with)->paginate(15);
        return AccountResource::collection($accounts);
    }

    /**
     * Display a listing of the resource to manage.
     *
     * @return \Illuminate\Http\Response
     */
    public function manage()
    {
        $search = $this->keyword;
        $_with = $this->requiredModelRelationships;
        $isManager = auth()->user()->can('manage', 'App\Models\Game');

        if ($isManager) {
            $baseQuery = new Account;
        } else {
            $baseQuery = auth()->user()->accounts();
        };

        $accounts = $baseQuery->where(
            fn ($query) =>  $query
                ->where('username', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%")
                ->orWhere('id', $search)
                ->orWhere('cost', $search)
        )
            ->with($_with)
            ->paginate(15);

        return AccountResource::collection($accounts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Account\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request, AccountType $accountType)
    {
        $usableStatusCode = auth()->user()->usableAccountTypes()
            ->where('account_type_id', $accountType->getKey())
            ->first()->pivot->status_code;
        $dataOfAccount = [];
        foreach ([
            'username', 'password', 'cost', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $snackKey = Str::snake($key);
                $dataOfAccount[$snackKey] = $request->$key;
            }
        }
        $dataOfAccount['account_type_id'] = $accountType->getKey();

        try {
            DB::beginTransaction();
            $imagePathsNeedDeleteWhenFail = [];

            // Save account in database
            $account = Account::create($dataOfAccount);
            $account->accountStatuses()->create([
                'short_description' => AccountStatus::SHORT_DESCRIPTION_OF_CREATED,
                'code' => $usableStatusCode,
            ]);

            // handle representative image
            $path
                = $request->representativeImage->store('public/account-images');
            $imagePathsNeedDeleteWhenFail[] = $path;
            $account->representativeImage()->create([
                'path' => $path,
                'type' => File::IMAGE_TYPE,
                'short_description' => File::SHORT_DESCRIPTION_OF_REPRESENTATIVE_IMAGE,
            ]);

            // Account infos
            $rawAccountInfos = array_map(fn ($field) => [
                'values' => $field['values']
            ], $request->rawAccountInfos ?? []);
            $account->accountInfos()->sync($rawAccountInfos);
            // Account actions
            $rawAccountActions = array_map(fn ($field) => [
                'is_done' => $field['isDone']
            ], $request->rawAccountActions ?? []);
            $account->accountActions()->sync($rawAccountActions);
            // game infos
            $rawGameInfos = array_map(fn ($field) => [
                'values' => $field['values']
            ], $request->rawGameInfos ?? []);
            $account->gameInfos()->sync($rawGameInfos);

            // handle sub account images
            if ($request->hasFile('images')) {
                foreach ($request->images as $image) {
                    $imagePath = $image->store('public/account-images');
                    $imagePathsNeedDeleteWhenFail[] = $imagePath;
                    $account->otherImages()->create([
                        'path' => $imagePath,
                        'type' => File::IMAGE_TYPE,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            Storage::delete($imagePathsNeedDeleteWhenFail);
            throw $th;
        }

        return AccountResource::withLoadRelationships($account->refresh());
    }

    /**
     * End approving account to publish account to user buyable or not publish.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function startApproving(Request $request, Account $account)
    {
        try {
            DB::beginTransaction();
            $account->accountStatuses()->create([
                'code' => 200,
                'short_description' => AccountStatus::SHORT_DESCRIPTION_OF_START_APPROVING,
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        // Done
        return response()->json([], 204);
    }

    /**
     * End approving account to publish account to user buyable or not publish.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function endApproving(Request $request, Account $account)
    {
        $newStatusCode = $account->accountType
            ->approvableUsers()->where('user_id', auth()->user()->id)
            ->first()->pivot->status_code;

        try {
            DB::beginTransaction();

            $account->accountStatuses()->create([
                'code' => $newStatusCode,
                'short_description' => AccountStatus::SHORT_DESCRIPTION_OF_END_APPROVING,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        // Done
        return response()->json([], 204);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        return AccountResource::withLoadMissingRelationships($account);
    }

    /**
     * Calculate detail price include cost and fee of account.
     *
     */
    public function getPrice(Request $request, Account $account): JsonResponse
    {
        return response()->json([
            'data' => ['data' => $account->calculatePrice($request->couponCode, true),],
        ]);
    }

    /**
     * Buy an account
     *
     */
    public function buy(Request $request, Account $account): JsonResponse
    {
        $bestPrice = $account->calculatePriceAndUseCouponNow($request->couponCode);

        try {
            DB::beginTransaction();
            $oldStatusCode = $account->latestAccountStatus->code;

            // Do something before send account for user
            switch ($oldStatusCode) {
                case 480:
                    $newStatusCode = 880;
                    break;
                case 440:
                    $newStatusCode = 840;
                    break;
                default:
                    abort(500, "Lack case handle for account with status $oldStatusCode.");
            }

            // Handle on user
            auth()->user()->reduceGoldCoin($bestPrice);

            // Handle on account
            $account->update([
                'buyer_id' => auth()->user()->getKey(),
                'sold_at_price' => $bestPrice,
                'sold_at' => now(),
            ]);
            $account->accountStatuses()->create([
                'code' => $newStatusCode,
                'short_description' => AccountStatus::SHORT_DESCRIPTION_OF_SOLD
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return response()->json([], 204);
    }

    /**
     * Update account infos of $account
     *
     * @param  \App\Http\Requests\Account\UpdateAccountInfosRequest  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function updateAccountInfos(UpdateAccountInfosRequest $request, Account $account)
    {
        try {
            DB::beginTransaction();
            $rawAccountInfos = array_map(fn ($field) => [
                'values' => $field['values']
            ], $request->rawAccountInfos ?? []);
            $account->accountInfos()->sync($rawAccountInfos);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return response()->json([], 204);
    }

    /**
     * Update game infos of $account
     *
     * @param  \App\Http\Requests\Account\UpdateGameInfosRequest  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function updateGameInfos(UpdateGameInfosRequest $request, Account $account)
    {
        try {
            DB::beginTransaction();
            $rawGameInfos = array_map(fn ($field) => [
                'values' => $field['values']
            ], $request->rawGameInfos ?? []);
            $account->gameInfos()->sync($rawGameInfos);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return response()->json([], 204);
    }

    /**
     * Update login infos of $account
     *
     * @param  \App\Http\Requests\Account\UpdateLoginInfosRequest  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function updateLoginInfos(UpdateLoginInfosRequest $request, Account $account)
    {
        $account->update([
            'username' => $request->username,
            'password' => $request->password,
        ]);

        return response()->json([], 204);
    }

    /**
     * Update images of $account
     *
     * @param  \App\Http\Requests\Account\UpdateImagesRequest  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function updateImages(UpdateImagesRequest $request, Account $account)
    {
        $deletedFilePathsWhenFail = [];
        try {
            DB::beginTransaction();

            if ($request->hasFile('representativeImage')) {
                optional($account->representativeImage)->forceDelete();
                $filePath = $request->representativeImage->store('public/account-images');
                $deletedFilePathsWhenFail[] = $filePath;
                $account->representativeImage()->create([
                    'path' => $filePath,
                    'short_description' => File::SHORT_DESCRIPTION_OF_REPRESENTATIVE_IMAGE,
                    'type' => File::IMAGE_TYPE,
                ]);
            }

            if ($request->hasFile('otherImages')) {
                $account->otherImages->each(fn ($file) => $file->forceDelete());
                foreach ($request->otherImages as $image) {
                    $filePath = $image->store('public/account-images');
                    $deletedFilePathsWhenFail[] = $filePath;
                    $account->representativeImage()->create([
                        'path' => $filePath,
                        'type' => File::IMAGE_TYPE,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Storage::delete($deletedFilePathsWhenFail);
            throw $th;
        }

        return response()->json([], 204);
    }

    /**
     * Update Cost of $account
     *
     * @param  \App\Http\Requests\Account\UpdateCostRequest  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function updateCost(UpdateCostRequest $request, Account $account)
    {
        $account->update([
            'cost' => $request->cost,
        ]);
        return response()->json([], 204);
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
            $account->delete(); // Delete account
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return response()->json([
            'message' => 'Xoá tài khoản thành công.',
        ], 200);
    }
}
