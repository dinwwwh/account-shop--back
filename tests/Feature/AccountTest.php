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
use App\Models\Account;
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

        $x = rand(2, 5);
        for ($zz = 0; $zz < $x; $zz++) {
            $accountType = AccountType::create([
                'name' => Str::random(40),
                'slug' => Str::random(40),
                'game_id' => $game->id,
            ]);
            $accountType->allowRole('tester', rand(0, 1) ? 0 : 440);

            $rand = rand(5, 10);
            for ($i = 0; $i < $rand; $i++) {
                $rule = Rule::create(['required' => true]);
                $accountInfo = AccountInfo::create([
                    'name' => Str::random(40),
                    'slug' => Str::random(40),
                    'rule_id' => $rule->id,
                    'account_type_id' => $accountType->id,
                ]);
                $accountInfo->rolesNeedFilling()->attach('tester');
            }

            $rand = rand(5, 10);
            for ($nn = 0; $nn < $rand; $nn++) {
                $accountAction = AccountAction::create([
                    'name' => Str::random(40),
                    'slug' => Str::random(40),
                    'video_path' => Str::random(40),
                    'account_type_id' => $accountType->id,
                    'required' => true,
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
        for ($aBc = 0; $aBc < 5; $aBc++) {
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
                'price' => rand(20000, 50000),
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

    public function testUpdate()
    {
        $account = Account::inRandomOrder()->first();
        $creator = $account->creator;
        $creator->givePermissionTo('update_account');
        $creator->refresh();

        $accountType = $account->accountType;
        $route = route('account.update', ['account' => $account]);

        $data = [
            'roleKey' => 'tester',
            'username' => Str::random(60),
            'password' => Str::random(60),
            'price' => rand(20000, 50000),
            'description' => Str::random(100),
            'representativeImage' => UploadedFile::fake()->image('avatar.jpg'),
            'images' => [
                UploadedFile::fake()->image('avatar343243.jpg'), UploadedFile::fake()->image('avatar4324.jpg')
            ],
            'accountInfos' =>  $this->makeDataForAccountInfos($accountType),
            'accountActions' => $this->makeDataForAccountActions($accountType),
        ];
        $res = $this->actingAs($creator)
            ->json('put', $route, $data);
        $res->assertStatus(200);
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

    public function testApprove()
    {
        $account = Account::inRandomOrder()
            ->where('status_code', '>=', 0)
            ->where('status_code', '<=', 99)
            ->first();
        $route = route('account.approve', ['account' => $account]);
        $user = User::factory()->make();
        $user->save();
        $user->givePermissionTo('approve_account');
        $user->refresh();

        $res = $this->actingAs($user)
            ->json('post', $route);

        $res->assertStatus(200);
        $res->assertJson(
            fn ($j) => $j
                ->where('data.statusCode', 480)
        );
    }

    public function testShow()
    {
        $account = Account::inRandomOrder()->first();

        $route = route('account.show', ['account' => $account]);

        $res = $this->json('get', $route);

        $res->assertStatus(200);

        $res->assertJson(
            fn ($j) => $j
                ->has(
                    'data',
                    fn ($j) => $j
                        ->where('id', $account->id)
                        ->where('username', $account->username)
                        ->where('password', $account->password)
                        ->where('price', $account->price)
                        ->where('statusCode', $account->status_code)
                        ->where('description', $account->description)
                        ->has('representativeImagePath')
                        ->has('lastRoleKeyCreatorUsed')
                        ->has('images')
                        ->has('game')
                        ->has('accountType')
                        ->has('lastUpdatedEditor')
                        ->has('creator')
                        ->has('censor')
                        ->has('type')
                        ->has('infos')
                        ->has('actions')
                        ->has('approvedAt')
                        ->has('updatedAt')
                        ->has('createdAt')
                )
        );
    }

    public function testBuy()
    {
        $account = Account::inRandomOrder()
            ->where('status_code', '>=', 400)
            ->where('status_code', '<=', 499)
            ->first();


        $route = route('account.buy', ['account' => $account]);
        $user = User::factory()->make();
        $goldCoin = rand(100000, 200000);
        $user->gold_coin = $goldCoin;
        $user->save();

        $res = $this->actingAs($user)
            ->json('post', $route);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($j) => $j
                ->where('data.username', $account->username)
                ->where('data.password', $account->password)
        );

        $res = $this->actingAs($user)
            ->json('get', route('profile.show'));
        $res->assertJson(
            fn ($j) => $j
                ->where('data.goldCoin',  $goldCoin - $account->price)
        );
    }
}
