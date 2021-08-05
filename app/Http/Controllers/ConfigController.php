<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConfigResource;
use App\Models\Config;
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
        //
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
     * @param  \App\Models\Config  $setting
     * @return \Illuminate\Http\Response
     */
    public function show(Config $setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Config  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Config $setting)
    {
        //
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
