<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use Illuminate\Http\Request;
use App\Http\Resources\RuleResource;
use DB;

class RuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return RuleResource::collection(Rule::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Initialize rule data
        $ruleData = [];
        foreach ([
            'type', 'datatype', 'required', 'multiple',
            'min', 'minlength', 'max', 'maxlength', 'values'
        ] as $key) {
            if ($request->filled($key)) {
                $ruleData[$key] = $request->$key;
            }
        }

        // DB transaction
        try {
            DB::beginTransaction();
            $rule = Rule::create($ruleData); // Save rule to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Tạo rule thất bại, vui lòng thừ lại sau.',
            ], 404);
        }

        return new RuleResource($rule->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rule  $rule
     * @return \Illuminate\Http\Response
     */
    public function show(Rule $rule)
    {
        return new RuleResource($rule);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rule  $rule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Rule $rule)
    {
        // Initialize rule data
        $ruleData = [];
        foreach ([
            'type', 'datatype', 'required', 'multiple',
            'min', 'minlength', 'max', 'maxlength', 'values'
        ] as $key) {
            if ($request->filled($key)) {
                $ruleData[$key] = $request->$key;
            }
        }

        // DB transaction
        try {
            DB::beginTransaction();
            $rule->update($ruleData); // Update rule to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Cập nhật rule thất bại, vui lòng thừ lại sau.',
            ], 404);
        }

        return new RuleResource($rule);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rule  $rule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rule $rule)
    {
        // DB transaction
        try {
            DB::beginTransaction();
            $rule->delete(); // Update rule to database
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Cập nhật rule thất bại, vui lòng thừ lại sau.',
            ], 404);
        }

        return response()->json([
            'message' => 'Xoá rule thành công.',
        ], 200);
    }
}
