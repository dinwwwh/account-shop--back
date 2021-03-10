<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Game;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use Str;
use DB;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
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
        $game = Game::find($request->gameId);
        if (is_null($game)) {
            return response()->json([
                'message' => 'ID game không hợp lệ.'
            ], 404);
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
        $accountData['creator_id'] = auth()->user()->id;
        $accountData['last_updated_editor_id'] = auth()->user()->id;

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
}
