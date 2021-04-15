<?php

namespace Tests\Feature;

use App\Models\AccountType;
use App\Models\AccountFee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AccountFeeTest extends TestCase
{
    public function testStore()
    {
        foreach ([1, 2, 3] as $nAnNnN) {
            $accountType = AccountType::inRandomOrder()->first();
            $route = route('account-fee.store', ['accountType' => $accountType]);
            $creator = $accountType->creator;
            $creator->givePermissionTo('update_account_type');
            $creator->refresh();

            $data = [
                'maximumCost' => rand(1, 10000),
                'minimumCost' => rand(1, 10000),
                'maximumFee' => rand(1, 10000),
                'minimumFee' => rand(1, 10000),
                'percentageCost' => rand(1, 100),
            ];

            $res = $this->actingAs($creator)
                ->json('post', $route, $data);
            $res->assertStatus(201);
            $res->assertJson(
                fn ($json) => $json
                    ->where('data.maximumCost', $data['maximumCost'])
                    ->where('data.minimumCost', $data['minimumCost'])
                    ->where('data.maximumFee', $data['maximumFee'])
                    ->where('data.minimumFee', $data['minimumFee'])
                    ->where('data.percentageCost', $data['percentageCost'])
            );
        }
    }

    public function testUpdate()
    {
        $accountFee = AccountFee::inRandomOrder()->first();
        $route = route('account-fee.update', ['accountFee' => $accountFee]);
        $creator = $accountFee->creator;

        $data = [
            'maximumCost' => rand(1, 10000),
            'minimumCost' => rand(1, 10000),
            'maximumFee' => rand(1, 10000),
            'minimumFee' => rand(1, 10000),
            'percentageCost' => rand(1, 100),
        ];

        $res = $this->actingAs($creator)
            ->json('put', $route, $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->where('data.maximumCost', $data['maximumCost'])
                ->where('data.minimumCost', $data['minimumCost'])
                ->where('data.maximumFee', $data['maximumFee'])
                ->where('data.minimumFee', $data['minimumFee'])
                ->where('data.percentageCost', $data['percentageCost'])
        );
    }

    public function testUpdateRouteMiddleware()
    {
        $accountFee = AccountFee::inRandomOrder()->first();
        $route = route('account-fee.update', ['accountFee' => $accountFee]);

        /**
         * Not Auth
         * -------------------------
         */
        $this->json('put', $route)
            ->assertStatus(401);

        /**
         * Auth
         * -------------------------
         * can update correspond account type
         */
        $user = User::factory()->make();
        $user->save();

        # 0
        $this->actingAs($user)
            ->json('put', $route)
            ->assertStatus(403);

        # 1
        $user->givePermissionTo('update_account_type');
        $user->givePermissionTo('manage_account_type');
        $user->refresh();
        $this->actingAs($user)
            ->json('put', $route)
            ->assertStatus(200);
    }

    public function testDestroy()
    {
        $accountFee = AccountFee::inRandomOrder()->first();
        $route = route('account-fee.destroy', ['accountFee' => $accountFee]);
        $creator = $accountFee->creator;


        $res = $this->actingAs($creator)
            ->json('delete', $route);
        $res->assertStatus(200);

        $this->json('put', route('account-fee.update', ['accountFee' => $accountFee]))
            ->assertStatus(404);
    }

    public function testStoreRouteMiddleware()
    {
        $accountType = AccountType::inRandomOrder()->first();
        $route = route('account-fee.store', ['accountType' => $accountType]);

        /**
         * Not Auth
         * -------------------------
         */
        $this->json('post', $route)
            ->assertStatus(401);

        /**
         * Auth
         * -------------------------
         * can update correspond account type
         */
        $user = User::factory()->make();
        $user->save();

        # 0
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(403);

        # 1
        $user->givePermissionTo('update_account_type');
        $user->givePermissionTo('manage_account_type');
        $user->refresh();
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(422);
    }
}
