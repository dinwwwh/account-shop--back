<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Game;
use App\Models\AccountType;
use Illuminate\Support\Str;

class AccountTypeTest extends TestCase
{
    public function testCreate()
    {
        // Initial data
        $user = User::factory()->make();
        $user->save();

        /**
         * Don't have power - no logged
         * -----------------------------
         */
        $res = $this->json('post', route('account-type.store'));
        $res->assertStatus(401);

        /**
         * Don't have power - logged
         * -----------------------------
         */
        $res = $this->actingAs($user)
            ->json('post', route('account-type.store'));
        $res->assertStatus(403);

        /**
         * Have power
         * -----------------------------
         */
        $user->givePermissionTo('create_account_type');
        $user->refresh();

        # Case: validate error
        $res = $this->actingAs($user)
            ->json('post', route('account-type.store'));
        $res->assertStatus(422);

        # Case: validate success
        $game = Game::factory()->make();
        $game->save();
        $data = [
            'gameId' => $game->id,
            'name' => Str::random(20),
            'description' => Str::random(20),
        ];
        $res = $this->actingAs($user)
            ->json('post', route('account-type.store'), $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->etc()
                )
        );
    }

    public function testRead()
    {
        // Initial data
        $accountType = AccountType::first();

        /**
         * Have power
         * -----------------------------
         */
        $res = $this->json('get', route('account-type.show', ['accountType' => $accountType]));
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('id', $accountType->id)
                        ->where('name', $accountType->name)
                        ->where('slug', $accountType->slug)
                        ->where('description', $accountType->description)
                        ->has('currentRoleNeedFillingAccountInfos')
                        ->has('accountInfos')
                        ->has('currentRoleNeedPerformingAccountActions')
                        ->has('accountActions')
                        ->has('lastUpdatedEditor')
                        ->has('creator')
                        ->has('updatedAt')
                        ->has('createdAt')
                )
        );
    }

    public function testUpdate()
    {
        // Initial data
        $user = User::factory()->make();
        $user->save();
        $accountType = AccountType::first();
        $creator = $accountType->creator;

        /**
         * Don't have power - no logged
         * -----------------------------
         */
        $res = $this->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(401);

        /**
         * Don't have power - user
         * -----------------------------
         */
        $res = $this->actingAs($user)
            ->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(403);

        /**
         * Don't have power - creator
         * -----------------------------
         */
        $creator->revokePermissionTo('update_account_type');
        $creator->refresh();

        $res = $this->actingAs($creator)
            ->json('put', route('account-type.update', ['accountType' => $accountType]));
        $res->assertStatus(403);

        /**
         * Have power - user
         * -----------------------------
         */
        $user->givePermissionTo('update_account_type', 'manage_account_type');
        $user->refresh();

        $data = [
            'name' => Str::random(20),
            'description' => Str::random(20),
        ];
        $res = $this->actingAs($user)
            ->json('put', route('account-type.update', ['accountType' => $accountType]), $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->etc()
                )
        );

        /**
         * Have power - creator
         * -----------------------------
         */
        $creator->givePermissionTo('update_account_type');
        $creator->refresh();

        $data = [
            'name' => Str::random(20),
            'description' => Str::random(20),
        ];
        $res = $this->actingAs($creator)
            ->json('put', route('account-type.update', ['accountType' => $accountType]), $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($json) => $json
                ->has(
                    'data',
                    fn ($json) => $json
                        ->where('name', $data['name'])
                        ->where('description', $data['description'])
                        ->etc()
                )
        );
    }

    // public function testDelete()
    // {
    // }
}
