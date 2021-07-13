<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGameInfoRequest;
use App\Http\Requests\UpdateGameInfoRequest;
use App\Http\Resources\GameInfoResource;
use App\Models\Game;
use App\Models\GameInfo;
use App\Models\Role;
use App\Models\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GameInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $_with = $this->requiredModelRelationships;
        $gameInfos = GameInfo::with($_with)->paginate(15);
        return GameInfoResource::collection($gameInfos);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreGameInfoRequest  $request
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGameInfoRequest $request, Game $game)
    {
        // Initialize data
        $gameInfoData = [];
        foreach ([
            'order', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $gameInfoData[Str::snake($key)] = $request->$key;
            }
        }
        $gameInfoData['name'] = $request->name;
        $gameInfoData['slug'] = Str::slug($gameInfoData['name']);
        $gameInfoData['game_id'] = $game->getKey();

        try {
            DB::beginTransaction();

            // rule relationship
            $rule = Rule::createQuickly($request->rule ?? []);
            $gameInfoData['rule_id'] = $rule->getKey();

            $gameInfo = GameInfo::create($gameInfoData);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return GameInfoResource::withLoadRelationships($gameInfo->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GameInfo  $gameInfo
     * @return \Illuminate\Http\Response
     */
    public function show(GameInfo $gameInfo)
    {
        return GameInfoResource::withLoadRelationships($gameInfo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateGameInfoRequest  $request
     * @param  \App\Models\GameInfo  $gameInfo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGameInfoRequest $request, GameInfo $gameInfo)
    {
        // Initialize data
        $gameInfoData = [];
        foreach ([
            'order', 'description', 'name'
        ] as $key) {
            if ($request->filled($key)) {
                $gameInfoData[Str::snake($key)] = $request->$key;
            }
        }
        if (array_key_exists('name', $gameInfoData)) {
            $gameInfoData['slug'] = Str::slug($gameInfoData['name']);
        }

        try {
            DB::beginTransaction();
            $gameInfo->update($gameInfoData);
            $gameInfo->rule->updateQuickly($request->rule ?? []);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return GameInfoResource::withLoadRelationships($gameInfo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GameInfo  $gameInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(GameInfo $gameInfo)
    {
        try {
            DB::beginTransaction();
            $gameInfo->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return response()->json([
            'message' => 'Xoá thông tin game thành công!',
        ], 200);
    }
}
