<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Role;
use App\Http\Resources\GameResource;
use Str;
use DB;
use Storage;
use App\Http\Requests\StoreGameRequest;
use App\Http\Requests\UpdateGameRequest;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $games = Game::all();
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

            // Relationship many-many with Models\Role
            $role = Role::all();
            $syncRoleIds = [];
            foreach ($request->roleIdsCanCreatedGame ?? [] as $roleId) {
                if ($role->contains($roleId)) {
                    $syncRoleIds[] = $roleId;
                }
            }
            $game->rolesCanCreatedGame()->sync($syncRoleIds);
            DB::commit();
        } catch (\Throwable $th) {
            return $th;
            DB::rollback();
            Storage::delete($imagePath);
            return response()->json([
                'message' => 'Thêm game thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return new GameResource($game->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function show(Game $game)
    {
        return new GameResource($game);
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
            'order', 'publisherName', 'name'
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

            // Relationship many-many with Models\Role
            $role = Role::all();
            $syncRoleIds = [];
            foreach ($request->roleIdsCanCreatedGame ?? [] as $roleId) {
                if ($role->contains($roleId)) {
                    $syncRoleIds[] = $roleId;
                }
            }
            $game->rolesCanCreatedGame()->sync($syncRoleIds);
            DB::commit();
            // handle when success
            Storage::delete($imagePathMustDeleteWhenSuccess ?? null);
        } catch (\Throwable $th) {
            return $th;
            DB::rollback();
            Storage::delete($imagePath ?? null);
            return response()->json([
                'message' => 'Chỉnh sửa game thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return new GameResource($game);
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

            // Delete relationship with Models\Role
            $game->rolesCanCreatedGame()->sync([]);

            $game->delete();
            DB::commit();
            // When success
            Storage::delete($imagePath);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Xoá game thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return response()->json([
            'message' => 'Xoá game thành công.',
        ], 200);
    }
}
