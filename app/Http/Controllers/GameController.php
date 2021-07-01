<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Role;
use App\Http\Resources\GameResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Models\DiscountCode;
use App\Http\Requests\Request;
use App\Http\Requests\AllowDiscountCodeInGameRequest;
use Illuminate\Database\Eloquent\Collection;


class GameController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $games = Game::with($this->_with)->paginate(5);
        return GameResource::collection($games);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGameRequest $request)
    {
        // Initialize data
        $gameData = [];
        foreach ([
            'order', 'publisherName', 'name'
        ] as $key) {
            if ($request->filled($key)) {
                $gameData[Str::snake($key)] = $request->$key;
            }
        }
        $gameData['slug'] = Str::slug($gameData['name']);
        $gameData['last_updated_editor_id'] = auth()->user()->id;
        $gameData['creator_id'] = auth()->user()->id;

        // DB transaction
        try {
            DB::beginTransaction();
            $imagePath = $request->image->store('/public/game-images');
            $gameData['image_path'] = $imagePath;
            $game = Game::create($gameData); // Save rule to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            Storage::delete($imagePath);
            throw $th;
        }

        return GameResource::withLoadRelationships($game->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function show(Game $game)
    {
        return GameResource::withLoadRelationships($game);
    }

    /**
     * Return all usable game that auth can use it to create an game
     *
     * @return \Illuminate\Http\Response
     */
    public function getUsableGame()
    {
        $roles = auth()->user()->roles()->with('accountType.game')->get();
        $games = new Collection;
        foreach ($roles as $role) {
            foreach ($role->accountTypes as $accountType) {
                if (!$games->contains($accountType->game)) {
                    $games->push($accountType->game);
                }
            }
        }
        return GameResource::collection($games);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGameRequest $request, Game $game)
    {
        // Initialize data
        $gameData = [];
        foreach ([
            'order', 'publisherName', 'name', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $gameData[Str::snake($key)] = $request->$key;
            }
        }
        if (array_key_exists('name', $gameData)) {
            $gameData['slug'] = Str::slug($gameData['name']);
        }
        $gameData['last_updated_editor_id'] = auth()->user()->id;

        // DB transaction
        try {
            DB::beginTransaction();
            // Handle image
            if ($request->hasFile('image')) {
                $imagePath = $request->image->store('/public/game-images');
                $gameData['image_path'] = $imagePath;
                $imagePathMustDeleteWhenSuccess = $game->image_path;
            }
            // Save rule to database
            $game->update($gameData);
            DB::commit();
            // handle when success
            Storage::delete($imagePathMustDeleteWhenSuccess ?? null);
        } catch (\Throwable $th) {
            DB::rollback();
            Storage::delete($imagePath ?? null);
            throw $th;
        }

        return GameResource::withLoadRelationships($game);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function destroy(Game $game)
    {
        // DB transaction
        try {
            DB::beginTransaction();
            $imagePath = $game->image_path;
            $game->delete();
            DB::commit();
            // When success
            Storage::delete($imagePath);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }

        return response()->json([
            'message' => 'Xoá game thành công.',
        ], 200);
    }

    /**
     * allow a discount code able use in $game to reduce price.
     *
     * @param App\Http\Requests\Request $request
     * @param App\Models\Game $game
     * @param App\Models\DiscountCode $discountCode
     * @return \Illuminate\Http\Response
     */
    public function allowDiscountCode(AllowDiscountCodeInGameRequest $request, Game $game, DiscountCode $discountCode)
    {
        $pivot = $request->typeCode ? ['type_code' => $request->typeCode] : [];
        try {
            DB::beginTransaction();
            $discountCode->supportedGames()->attach($game->getKey(), $pivot);
            DB::commit();
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            throw $th;
        }

        return response()->json([
            'message' => 'Cho phép phiếu giảm giá ' .
                $discountCode->discount_code .
                ' được phép sử dụng trong '
                . $game->name .
                ' thành công!',
        ], 200);
    }
}
