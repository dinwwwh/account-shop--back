<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Str;
use Tests\TestCase;

class UpdateTest extends Helper
{
    public function test_controller_and_request()
    {
        $account = Account::inRandomOrder()
            ->whereIn('status_code', [0, 440])
            ->first();
        $this->actingAs($this->makeAuth([]));

        $accountType = $account->accountType;
        $route = route('account.update', ['account' => $account]);

        $data = [
            'roleKey' => 'tester',
            'username' => Str::random(60),
            'password' => Str::random(60),
            'cost' => rand(20000, 50000),
            'description' => Str::random(100),
            'representativeImage' => UploadedFile::fake()->image('avatar.jpg'),
            'images' => [
                UploadedFile::fake()->image('avatar343243.jpg'), UploadedFile::fake()->image('avatar4324.jpg')
            ],
            'accountInfos' =>  $this->makeDataForAccountInfos($accountType),
            'accountActions' => $this->makeDataForAccountActions($accountType),
            'gameInfos' => $this->makeDataForGameInfos($account->accountType->game),
            '_requiredModelRelationships' => ['representativeImage', 'otherImages']
        ];
        $res = $this->json('put', $route, $data);
        $res->assertStatus(200);
        $res->assertJson(
            fn ($j) => $j
                ->has(
                    'data',
                    fn ($j) => $j
                        ->where('username', $data['username'])
                        ->where('password', $data['password'])
                        ->where('cost', $data['cost'])
                        ->where('description', $data['description'])
                        ->has('representativeImage.path')
                        ->has('otherImages.' . array_key_last($data['images']))
                        ->etc()
                )
        );
        foreach ($data['accountInfos'] as $key => $value) {
            $this->assertDatabaseHas('account_account_info', [
                'account_id' => $res->getData()->data->id,
                'account_info_id' => (int)trim($key, 'id '),
                'value' => json_encode($value),
            ]);
        }
        foreach ($data['accountActions'] as $key => $value) {
            $this->assertDatabaseHas('account_account_action', [
                'account_id' => $res->getData()->data->id,
                'account_action_id' => (int)trim($key, 'id '),
                'is_done' => (bool)$value,
            ]);
        }
        foreach ($data['gameInfos'] as $key => $value) {
            $this->assertDatabaseHas('account_has_game_infos', [
                'account_id' => $res->getData()->data->id,
                'game_info_id' => (int)trim($key, 'id '),
                'value' => json_encode($value),
            ]);
        }

        $intactData = $data;
        # Case: lack a part of accountInfo
        $firstKeyAccountInfo = array_key_first($data['accountInfos']);
        unset($data['accountInfos'][$firstKeyAccountInfo]);
        $res = $this->json('put', $route, $data);
        $res->assertStatus(422);
        $res->assertJson(
            fn ($j) => $j
                ->has('errors.accountInfos.' . $firstKeyAccountInfo)
                ->etc()
        );

        # Case: lack lack a part of accountAction
        $data = $intactData;
        $firstKeyAccountAction = array_key_first($data['accountActions']);
        unset($data['accountActions'][$firstKeyAccountAction]);
        $res = $this->json('put', $route, $data);
        $res->assertStatus(422);
        $res->assertJson(
            fn ($j) => $j
                ->has('errors.accountActions.' . $firstKeyAccountAction)
                ->etc()
        );

        # Case: invalid roleKey
        $data = $intactData;
        $data['roleKey'] = Str::random(10);
        $res = $this->json('put', $route, $data);
        $res->assertStatus(422);
        $res->assertJson(
            fn ($json) => $json
                ->has('errors.roleKey')
                ->etc()
        );
    }
}
