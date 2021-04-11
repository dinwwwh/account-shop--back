<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccountAction;
use App\Models\AccountInfo;
use App\Models\AccountType;
use App\Models\Rule;
use App\Models\Role;
use Illuminate\Support\Str;
use App\Models\Game;
use Illuminate\Http\UploadedFile;

class AccountTest extends TestCase
{
    public function makeIdealGame()
    {
        $user = User::factory()->make();
        $user->save();
        $this->actingAs($user);

        $game = Game::create([
            'publisher_name' => 'Sohagame',
            'name' => Str::random(40),
            'slug' => Str::random(40),
            'image_path' => Str::random(40),
        ]);
        $game->rolesCanCreatedGame()->sync('tester');

        $x = rand(3, 5);
        for ($zz = 0; $zz < $x; $zz++) {
            $accountType = AccountType::create([
                'name' => Str::random(40),
                'slug' => Str::random(40),
                'game_id' => $game->id,
            ]);
            $accountType->rolesCanUsedAccountType()->sync('tester');

            $rand = rand(3, 5);
            for ($i = 0; $i < $rand; $i++) {
                $rule = Rule::create(['required' => $i % 2 == 0 ? true : false]);
                $accountInfo = AccountInfo::create([
                    'name' => Str::random(40),
                    'slug' => Str::random(40),
                    'rule_id' => $rule->id,
                    'account_type_id' => $accountType->id,
                ]);
                $accountInfo->rolesNeedFilling()->attach('tester');
            }

            $rand = rand(3, 5);
            for ($i = 0; $i < $rand; $i++) {
                $accountAction = AccountAction::create([
                    'name' => Str::random(40),
                    'slug' => Str::random(40),
                    'video_path' => Str::random(40),
                    'account_type_id' => $accountType->id,
                    'required' => $i % 2 == 0 ? true : false,
                ]);
                $accountAction->rolesThatNeedPerformingAccountAction()->attach('tester');
            }
        }

        return $game;
    }

    public function makeDataForAccountInfos(AccountType $accountType)
    {
        $accountInfos = $accountType->accountInfosThatRoleNeedFilling(Role::find('tester'));
        $data = [];

        foreach ($accountInfos as $accountInfo) {
            if ($accountInfo->rule->required) {
                $data['id' . $accountInfo->getKey()] = Str::random(40);
            }
        }

        return $data;
    }

    public function makeDataForAccountActions(AccountType $accountType)
    {
        $accountActions = $accountType->accountActionsThatRoleNeedPerforming(Role::find('tester'));
        $data = [];

        foreach ($accountActions as $accountAction) {
            if ($accountAction->required) {
                $data['id' . $accountAction->getKey()] = true;
            }
        }

        return $data;
    }

    public function testStore()
    {
        $user = User::factory()->make();
        $user->save();
        $user->givePermissionTo('create_account');
        $user->assignRole('tester');
        $user->refresh();

        $game = $this->makeIdealGame();
        $accountType = $game->accountTypes->random();
        $route = route('account.store', ['accountType' => $accountType]);
        $dataOfAccountActions = $this->makeDataForAccountActions($accountType);
        $dataOfAccountInfos = $this->makeDataForAccountInfos($accountType);
        $data = [
            'roleKey' => 'tester',
            'username' => Str::random(60),
            'password' => Str::random(60),
            'price' => rand(20000, 200000000),
            'description' => Str::random(100),
            'representativeImage' => UploadedFile::fake()->image('avatar.jpg'),
            'images' => [
                UploadedFile::fake()->image('avatar343243.jpg'), UploadedFile::fake()->image('avatar4324.jpg')
            ],
            'accountInfos' => $dataOfAccountInfos,
            'accountActions' => $dataOfAccountActions,
        ];

        $res = $this->actingAs($user)
            ->json('post', $route, $data);
        $res->assertStatus(201);
        $res->assertJson(
            fn ($j) => $j
                ->has(
                    'data',
                    fn ($j) => $j
                        ->where('username', $data['username'])
                        ->where('password', $data['password'])
                        ->where('price', $data['price'])
                        ->where('description', $data['description'])
                        ->has('representativeImagePath')
                        ->has('images.' . array_key_last($data['images']))
                        ->has('infos.' . (count($data['accountInfos']) - 1))
                        ->has('actions.' . (count($data['accountActions']) - 1))
                        ->etc()
                )
        );
    }
}
