<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductSuggestionTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));
    }

    public function test_admin_creates_product_with_suggestions(): void
    {
        $this->actingAsAdmin();
        $suggested = Product::factory()->create();

        $this->postJson('/api/products', [
            'name' => 'Vestido',
            'price' => 100,
            'category_id' => $suggested->category_id,
            'suggestion_ids' => [$suggested->id],
        ])->assertCreated()->assertJsonPath('suggestions.0.id', $suggested->id);
    }

    public function test_suggestions_keep_the_chosen_order_and_can_be_replaced(): void
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create();
        [$first, $second, $third] = Product::factory()->count(3)->create();

        $this->putJson("/api/products/{$product->id}", ['suggestion_ids' => [$third->id, $first->id]])
            ->assertOk()
            ->assertJsonPath('suggestions.*.id', [$third->id, $first->id]);

        $this->putJson("/api/products/{$product->id}", ['suggestion_ids' => [$second->id, $third->id]])
            ->assertOk()
            ->assertJsonPath('suggestions.*.id', [$second->id, $third->id]);

        $this->putJson("/api/products/{$product->id}", ['suggestion_ids' => []])
            ->assertOk()
            ->assertJsonCount(0, 'suggestions');
    }

    public function test_update_without_the_field_keeps_the_suggestions(): void
    {
        $this->actingAsAdmin();
        $suggested = Product::factory()->create();
        $product = Product::factory()->create();
        $product->suggestions()->attach($suggested);

        $this->putJson("/api/products/{$product->id}", ['name' => 'Novo nome'])
            ->assertOk()
            ->assertJsonPath('suggestions.*.id', [$suggested->id]);
    }

    public function test_inactive_suggestions_are_only_visible_to_admin(): void
    {
        $product = Product::factory()->create();
        $active = Product::factory()->create();
        $inactive = Product::factory()->create(['active' => false]);
        $product->suggestions()->attach([
            $active->id => ['position' => 0],
            $inactive->id => ['position' => 1],
        ]);

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('suggestions.*.id', [$active->id]);

        $this->actingAsAdmin();
        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('suggestions.*.id', [$active->id, $inactive->id]);
    }

    public function test_invalid_suggestions_are_rejected(): void
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create();
        $other = Product::factory()->create();
        $tooMany = Product::factory()->count(Product::MAX_SUGGESTIONS + 1)->create()->modelKeys();
        $url = "/api/products/{$product->id}";

        $this->putJson($url, ['suggestion_ids' => [$product->id]])
            ->assertUnprocessable()->assertJsonValidationErrors('suggestion_ids.0');
        $this->putJson($url, ['suggestion_ids' => [$other->id, $other->id]])
            ->assertUnprocessable()->assertJsonValidationErrors('suggestion_ids.0');
        $this->putJson($url, ['suggestion_ids' => [fake()->uuid()]])
            ->assertUnprocessable()->assertJsonValidationErrors('suggestion_ids.0');
        $this->putJson($url, ['suggestion_ids' => $tooMany])
            ->assertUnprocessable()->assertJsonValidationErrors('suggestion_ids');

        $this->assertDatabaseEmpty('product_suggestions');
    }

    public function test_customer_cannot_change_suggestions(): void
    {
        $product = Product::factory()->create();
        $other = Product::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/products/{$product->id}", ['suggestion_ids' => [$other->id]])
            ->assertForbidden();

        $this->assertDatabaseEmpty('product_suggestions');
    }

    public function test_deleting_a_suggested_product_removes_it_from_the_list(): void
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create();
        $suggested = Product::factory()->create();
        $product->suggestions()->attach($suggested);

        $this->deleteJson("/api/products/{$suggested->id}")->assertOk();

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonCount(0, 'suggestions');
    }
}
