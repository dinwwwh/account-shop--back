<?php

namespace App\Http\Controllers;

use App\Http\Requests\Config\UpdateRequest;
use App\Http\Resources\ConfigResource;
use App\Models\Config;
use DB;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $configs = Config::with($this->requiredModelRelationships)->paginate(15);

        return ConfigResource::collection($configs);
    }

    /**
     * Get public settings of app.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPublicConfigs()
    {
        $publicConfigs = Config::where('public', true)->get();
        return ConfigResource::withLoadMissingRelationships($publicConfigs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Config $config)
    {
        return ConfigResource::withLoadMissingRelationships($config);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(UpdateRequest $request, Config $config)
    {
        try {
            DB::beginTransaction();
            $config->update($request->only([
                'data',
                'description',
            ]));
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return response()->json([
            'message' => 'Config was updated successfully.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Config  $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Config $setting)
    {
        //
    }
}
