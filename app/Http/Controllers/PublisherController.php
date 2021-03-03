<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use Illuminate\Http\Request;
use App\Http\Resources\PublisherResource;
use Str;
use DB;
use Auth;
use App\Http\Requests\StorePublisher;
use App\Http\Requests\UpdatePublisher;

class PublisherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return PublisherResource::collection(Publisher::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePublisher $request)
    {
        // Initialize data
        $publisherData = [];
        foreach ([
            'name', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $publisherData[$key] = $request->$key;
            }
        }
        $publisherData['slug'] = Str::slug($publisherData['name']);
        $publisherData['last_updated_editor_id'] = Auth::user()->id;
        $publisherData['creator_id'] = Auth::user()->id;

        // DB transaction
        try {
            DB::beginTransaction();
            $publisher = Publisher::create($publisherData); // Save rule to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Thêm nhà phát hành thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return new PublisherResource($publisher->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\Response
     */
    public function show(Publisher $publisher)
    {
        return new PublisherResource($publisher);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePublisher $request, Publisher $publisher)
    {
        // Initialize data
        $publisherData = [];
        foreach ([
            'name', 'description'
        ] as $key) {
            if ($request->filled($key)) {
                $publisherData[$key] = $request->$key;
            }
        }
        if (array_key_exists('name', $publisherData)) {
            $publisherCheck = Publisher::where('name', $publisherData['name'])->first();
            if (!is_null($publisherCheck) && $publisherCheck->id != $publisher->id) {
                return response()->json([
                    'errors' => ['name' => 'The name has already been taken.']
                ], 422);
            }
            $publisherData['slug'] = Str::slug($publisherData['name']);
        }
        $publisherData['last_updated_editor_id'] = Auth::user()->id;

        // DB transaction
        try {
            DB::beginTransaction();
            $publisher->update($publisherData); // Save rule to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Cập nhật thông tin nhà phát hành thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return new PublisherResource($publisher);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Publisher $publisher)
    {
        // DB transaction
        try {
            DB::beginTransaction();
            $publisher->delete(); // Update publisher to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Xoá nhà phát hành thất bại, vui lòng thừ lại sau.',
            ], 500);
        }

        return response()->json([
            'message' => 'Xoá nhà phát hành thành công.',
        ], 200);
    }
}
