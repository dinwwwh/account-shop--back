<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use Illuminate\Http\Request;
use App\Http\Resources\RuleResource;
use DB;
use App\Http\Requests\StoreRuleRequest;
use App\Http\Requests\UpdateRuleRequest;

class RuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rules = Rule::with($this->requiredModelRelationships)->paginate(15);
        return RuleResource::collection($rules);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRuleRequest $request)
    {
        // Initialize rule data
        $ruleData = [];
        foreach ([
            'type', 'datatype', 'required', 'multiple', 'placeholder',
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
            throw $th;
        }

        return RuleResource::withLoadRelationships($rule->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rule  $rule
     * @return \Illuminate\Http\Response
     */
    public function show(Rule $rule)
    {
        return RuleResource::withLoadRelationships($rule);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rule  $rule
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRuleRequest $request, Rule $rule)
    {
        // Initialize rule data
        $ruleData = [];
        foreach ([
            'type', 'datatype', 'required', 'multiple', 'placeholder',
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
            throw $th;
        }

        return RuleResource::withLoadRelationships($rule);
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
            throw $th;
        }

        return response()->json([
            'message' => 'Xoá rule thành công.',
        ], 200);
    }
}
