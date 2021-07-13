<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Storage;
use Str;
use Tests\TestCase;

class UpdateImagesTest extends Helper
{
    public function test_controller()
    {
        $account = Account::inRandomOrder()->first();
        $route = route('account.update-images', ['account' => $account]);
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $data = [
            'representativeImage' => UploadedFile::fake()->image('representative-image.jpg'),
            'otherImages' => [
                UploadedFile::fake()->image('other-image-0.jpg'),
                UploadedFile::fake()->image('other-image-1.jpg'),
            ],
        ];

        $res = $this->json('patch', $route, $data);
        $res->assertStatus(204);

        $account->refresh();
        $this->assertEquals(
            'public/account-images/' . $data['representativeImage']->hashName(),
            $account->representativeImage->getRawOriginal('path'),
        );
        $this->assertEquals(
            2,
            $account->otherImages()->count(),
        );

        Storage::assertExists('public/account-images/' . $data['representativeImage']->hashName());
        Storage::assertExists('public/account-images/' . $data['otherImages'][0]->hashName());
        Storage::assertExists('public/account-images/' . $data['otherImages'][1]->hashName());
    }

    public function test_middleware_success_manager()
    {
        $account = Account::inRandomOrder()->first();
        $route = route('account.update-images', ['account' => $account]);
        $user = $this->makeAuth([]);
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(204);
    }

    public function test_middleware_success_creator()
    {
        $config = config('account.creator.updatable_images_status_codes', []);
        if (empty($config)) {
            $this->assertTrue(true);
            return;
        }
        do {
            $account = Account::inRandomOrder()->first();
        } while (!in_array(
            $account->latestAccountStatus->code,
            $config
        ));

        $route = route('account.update-images', ['account' => $account]);
        $user = $this->makeAuth([], $account->creator, true);
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(204);
    }

    public function test_middleware_success_approver()
    {
        $config = config('account.approver.updatable_images_status_codes', []);
        $count = 0;
        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (
            !in_array(
                $account->latestAccountStatus->code,
                $config
            )
        );

        $route = route('account.update-images', ['account' => $account]);
        $user = $this->makeAuth([], $account->latestAccountStatus->creator, true);
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(204);
    }

    public function test_middleware_fail_manager()
    {
        $account = Account::inRandomOrder()->first();
        $route = route('account.update-images', ['account' => $account]);
        $user = $this->makeAuth(['update_game']);
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }

    public function test_middleware_fail_creator()
    {
        $config = config('account.creator.updatable_images_status_codes', []);
        $count = 0;
        do {
            $account = Account::inRandomOrder()->first();
            $count++;
            if ($count == 100) return;
        } while (
            in_array(
                $account->latestAccountStatus->code,
                $config
            )
        );

        $route = route('account.update-images', ['account' => $account]);
        $user = $this->makeAuth([], $account->creator, true);
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }

    public function test_middleware_fail_approver()
    {
        $count = 0;
        $config = config('account.approver.updatable_images_status_codes', []);
        do {
            $account = Account::inRandomOrder()->first();
            $approver = $account->accountType->approvableUsers()->where('user_id', '!=', $account->creator_id)->first();
            $count++;
            if ($count == 100) {
                return;
            }
        } while (
            in_array(
                $account->latestAccountStatus->code,
                $config
            )
            || is_null($approver)
        );

        $route = route('account.update-images', ['account' => $account]);
        $user = $this->makeAuth([], $approver, true);
        $this->actingAs($user);
        $res = $this->json('patch', $route);
        $res->assertStatus(403);
    }
}
