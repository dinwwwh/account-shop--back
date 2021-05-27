<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class DiscountCodeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testStore()
    {
        $route = route('discount-code.store');
        $data = [
            'discountCode' => Str::random(40),
            'price' => rand(10, 100000),
            'buyable' => rand(0, 1) ? true : false,
            'name' => Str::random(),
            'description' => Str::random(),
            'maximumPrice' => rand(10000, 100000),
            'minimumPrice' => rand(1000, 10000),
            'maximumDiscount' => rand(10000, 20000),
            'minimumDiscount' => rand(1000, 2000),
            'percentageDiscount' => rand(1, 100),
            'directDiscount' => rand(0, 20000),
            'usableAt' => Carbon::now(),
            'usableClosedAt' => Carbon::now(),
            'offeredAt' => Carbon::now(),
            'offerClosedAt' => Carbon::now(),
        ];
        $user = User::inRandomOrder()->first();
        $user->givePermissionTo('create_discount_code');
        $user->refresh();

        $res =  $this->actingAs($user)
            ->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json
                ->where('data.discountCode', $data['discountCode'])
                ->where('data.price', $data['price'])
                ->where('data.buyable', $data['buyable'])
                ->where('data.name', $data['name'])
                ->where('data.description', $data['description'])
                ->where('data.maximumPrice', $data['maximumPrice'])
                ->where('data.minimumPrice', $data['minimumPrice'])
                ->where('data.maximumDiscount', $data['maximumDiscount'])
                ->where('data.minimumDiscount', $data['minimumDiscount'])
                ->where('data.percentageDiscount', $data['percentageDiscount'])
                ->where('data.directDiscount', $data['directDiscount'])
                ->has('data.usableAt')
                ->has('data.usableClosedAt')
                ->has('data.offeredAt')
                ->has('data.offerClosedAt')
        );
    }

    public function testMultipleStore()
    {
        for ($i = 0; $i < 4; $i++) {
            $this->testStore();
        }
    }

    public function testStoreRouteMiddleware()
    {
        $route = route('discount-code.store');

        /**
         * Auth
         * --------------------
         */
        $user = User::inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

        # Can't create
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(403);


        # Can create
        $user->givePermissionTo('create_discount_code');
        $user->refresh();
        $this->actingAs($user)
            ->json('post', $route)
            ->assertStatus(422);
    }

    public function testResource()
    {
        $discountCode = DiscountCode::inRandomOrder()->first();
        $route = route('discount-code.show', ['discountCode' => $discountCode]);

        $res = $this->json('get', $route);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($j) => $j
                ->has(
                    'data',
                    fn ($j) => $j
                        ->where('discountCode', $discountCode->getKey())
                        ->where('price', $discountCode->price)
                        ->where('buyable', $discountCode->buyable)
                        ->where('name', $discountCode->name)
                        ->where('description', $discountCode->description)
                        ->where('maximumPrice', $discountCode->maximum_price)
                        ->where('minimumPrice', $discountCode->minimum_price)
                        ->where('maximumDiscount', $discountCode->maximum_discount)
                        ->where('minimumDiscount', $discountCode->minimum_discount)
                        ->where('percentageDiscount', $discountCode->percentage_discount)
                        ->where('directDiscount', $discountCode->direct_discount)
                        ->has('usableAt')
                        ->has('usableClosedAt')
                        ->has('offeredAt')
                        ->has('offerClosedAt')
                        ->has('lastUpdatedEditor')
                        ->has('creator')
                        ->has('updatedAt')
                        ->has('createdAt')
                        ->has('pivot')
                        ->has('buyers')
                )
        );
    }

    public function testUpdate()
    {
        $discountCode = DiscountCode::inRandomOrder()->first();
        $creator = $discountCode->creator;
        $creator->givePermissionTo('update_discount_code');
        $creator->refresh();

        $route = route('discount-code.update', ['discountCode' => $discountCode]);
        $data = [
            'price' => rand(10, 100000),
            'buyable' => rand(0, 1) ? true : false,
            'name' => Str::random(),
            'description' => Str::random(),
            'maximumPrice' => rand(10000, 100000),
            'minimumPrice' => rand(1000, 10000),
            'maximumDiscount' => rand(10000, 20000),
            'minimumDiscount' => rand(1000, 2000),
            'percentageDiscount' => rand(1, 100),
            'directDiscount' => rand(0, 20000),
            'usableAt' => Carbon::now(),
            'usableClosedAt' => Carbon::now(),
            'offeredAt' => Carbon::now(),
            'offerClosedAt' => Carbon::now(),
        ];

        $res =  $this->actingAs($creator)
            ->json('put', $route, $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->where('data.price', $data['price'])
                ->where('data.buyable', $data['buyable'])
                ->where('data.name', $data['name'])
                ->where('data.description', $data['description'])
                ->where('data.maximumPrice', $data['maximumPrice'])
                ->where('data.minimumPrice', $data['minimumPrice'])
                ->where('data.maximumDiscount', $data['maximumDiscount'])
                ->where('data.minimumDiscount', $data['minimumDiscount'])
                ->where('data.percentageDiscount', $data['percentageDiscount'])
                ->where('data.directDiscount', $data['directDiscount'])
                ->has('data.usableAt')
                ->has('data.usableClosedAt')
                ->has('data.offeredAt')
                ->has('data.offerClosedAt')
        );
    }

    public function testUpdateRouteMiddleware()
    {
        $discountCode = DiscountCode::inRandomOrder()->first();
        $route = route('discount-code.update', ['discountCode' => $discountCode]);

        /**
         * Auth
         * --------------------
         * Update - Manage
         */
        $creator = $discountCode->creator;
        $creator->syncPermissions();
        $creator->syncRoles();
        $creator->refresh();
        $user = User::whereNotIn('id', [$creator->getKey()])
            ->inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

        # 0 - 0 (as user)
        $this->actingAs($user)
            ->json('put', $route)
            ->assertStatus(403);
        # 0 - 0 (as creator)
        $this->actingAs($creator)
            ->json('put', $route)
            ->assertStatus(403);
        # 0 - 1 (as user)
        $user->givePermissionTo('manage_discount_code');
        $user->refresh();
        $this->actingAs($user)
            ->json('put', $route)
            ->assertStatus(403);
        # 0 - 1 (as creator)
        $creator->givePermissionTo('manage_discount_code');
        $creator->refresh();
        $this->actingAs($creator)
            ->json('put', $route)
            ->assertStatus(403);
        # 1 - 0 (as user)
        $user->revokePermissionTo('manage_discount_code');
        $user->givePermissionTo('update_discount_code');
        $user->refresh();
        $this->actingAs($user)
            ->json('put', $route)
            ->assertStatus(403);
        # 1 - 0 (as creator)
        $creator->revokePermissionTo('manage_discount_code');
        $creator->givePermissionTo('update_discount_code');
        $creator->refresh();
        $this->actingAs($creator)
            ->json('put', $route)
            ->assertStatus(200);
        # 1 - 1 (as user)
        $user->givePermissionTo('manage_discount_code');
        $user->refresh();
        $this->actingAs($user)
            ->json('put', $route)
            ->assertStatus(200);
        # 1 - 1 (as creator)
        $creator->givePermissionTo('manage_discount_code');
        $creator->refresh();
        $this->actingAs($creator)
            ->json('put', $route)
            ->assertStatus(200);
    }

    public function testDestroy()
    {
        $discountCode = DiscountCode::inRandomOrder()->first();
        $creator = $discountCode->creator;
        $creator->assignRole('administrator');
        $creator->refresh();
        $buyers = $discountCode->buyers;
        $supportedGames = $discountCode->supportedGames;
        $route = route('discount-code.destroy', ['discountCode' => $discountCode]);

        $res = $this->actingAs($creator)
            ->json('delete', $route);

        $res->assertStatus(200);
        $this->assertDatabaseMissing('discount_codes', [
            'discount_code' => $discountCode->getKey()
        ]);
        foreach ($buyers as $buyer) {
            $this->assertDatabaseMissing('discount_code_has_been_bought_by_users', [
                'discount_code' => $discountCode->getKey(),
                'user_id' => $buyer->getKey(),
            ]);
        }
        foreach ($supportedGames as $game) {
            $this->assertDatabaseMissing('discount_code_supports_games', [
                'discount_code' => $discountCode->getKey(),
                'game_id' => $game->getKey(),
            ]);
        }
    }

    public function testDestroyRouteMiddleware()
    {
        $discountCode = DiscountCode::inRandomOrder()->first();
        $route = route('discount-code.update', ['discountCode' => $discountCode]);
        $creator = $discountCode->creator;
        $creator->syncPermissions();
        $creator->syncRoles();
        $creator->refresh();
        $user = User::whereNotIn('id', [$creator->getKey()])
            ->inRandomOrder()->first();
        $user->syncPermissions();
        $user->syncRoles();
        $user->refresh();

        /**
         * Auth
         * --------------------
         * delete - manage
         */

        # Case 0 - 0 (as user)
        $this->actingAs($user)
            ->json('delete', $route)
            ->assertStatus(403);

        # Case 0 - 0 (as creator)
        $this->actingAs($creator)
            ->json('delete', $route)
            ->assertStatus(403);

        # Case 0 - 1 (as user)
        $user->givePermissionTo('manage_discount_code');
        $user->refresh();
        $this->actingAs($user)
            ->json('delete', $route)
            ->assertStatus(403);

        # Case 0 - 1 (as creator)
        $creator->givePermissionTo('manage_discount_code');
        $creator->refresh();
        $this->actingAs($creator)
            ->json('delete', $route)
            ->assertStatus(403);

        # Case 1 - 0 (as user)
        $user->revokePermissionTo('manage_discount_code');
        $user->givePermissionTo('delete_discount_code');
        $user->refresh();
        $this->actingAs($user)
            ->json('delete', $route)
            ->assertStatus(403);

        # Case 1 - 0 (as creator)
        $creator->revokePermissionTo('manage_discount_code');
        $creator->givePermissionTo('delete_discount_code');
        $creator->refresh();
        $this->actingAs($creator)
            ->json('delete', $route)
            ->assertStatus(200);

        // Refresh discount code since discount code has been deleted
        $discountCode = DiscountCode::inRandomOrder()->first();
        $route = route('discount-code.update', ['discountCode' => $discountCode]);
        $creator = $discountCode->creator;
        $creator->syncPermissions();
        $creator->syncRoles();
        $creator->refresh();

        # Case 1 - 1 (as user)
        $user->givePermissionTo('manage_discount_code');
        $user->refresh();
        $this->actingAs($user)
            ->json('delete', $route)
            ->assertStatus(200);

        // Refresh discount code since discount code has been deleted
        $discountCode = DiscountCode::inRandomOrder()->first();
        $route = route('discount-code.update', ['discountCode' => $discountCode]);
        $creator = $discountCode->creator;
        $creator->syncPermissions();
        $creator->syncRoles();
        $creator->refresh();

        # Case 1 - 1 (as creator)
        $creator->givePermissionTo('manage_discount_code', 'delete_discount_code');
        $creator->refresh();
        $this->actingAs($creator)
            ->json('delete', $route)
            ->assertStatus(200);
    }
}
