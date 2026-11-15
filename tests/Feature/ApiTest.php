<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Sanctum::actingAs($admin);

        return $admin;
    }

    public function test_register_login_me_and_logout(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Maria', 'email' => 'maria@teste.com', 'password' => 'senha123',
        ])->assertCreated()
            ->assertJsonPath('user.role', User::ROLE_CUSTOMER)
            ->assertJsonStructure(['user', 'token']);

        $token = $this->postJson('/api/auth/login', [
            'email' => 'maria@teste.com', 'password' => 'senha123',
        ])->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/auth/me')
            ->assertOk()->assertJsonPath('email', 'maria@teste.com');

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        User::factory()->create(['email' => 'a@a.com']);

        $this->postJson('/api/auth/login', ['email' => 'a@a.com', 'password' => 'errada'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_register_admin_requires_valid_code(): void
    {
        $payload = ['name' => 'Adm', 'email' => 'adm@teste.com', 'password' => 'senha123'];

        config(['auth.admin_register_code' => '']);
        $this->postJson('/api/auth/register-admin', [...$payload, 'admin_code' => 'x'])->assertForbidden();

        config(['auth.admin_register_code' => 'segredo']);
        $this->postJson('/api/auth/register-admin', [...$payload, 'admin_code' => 'x'])
            ->assertUnprocessable()->assertJsonValidationErrors('admin_code');

        $this->postJson('/api/auth/register-admin', [...$payload, 'admin_code' => 'segredo'])
            ->assertCreated()->assertJsonPath('user.role', User::ROLE_ADMIN);
    }

    public function test_customer_cannot_write_catalog(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/categories', ['name' => 'Nova'])->assertForbidden();
        $this->getJson('/api/users')->assertForbidden();
    }

    public function test_category_crud_and_delete_rule(): void
    {
        $this->actingAsAdmin();

        $id = $this->postJson('/api/categories', ['name' => 'Vestidos de Festa'])
            ->assertCreated()->assertJsonPath('slug', 'vestidos-de-festa')->json('id');

        $this->putJson("/api/categories/{$id}", ['name' => 'Festa'])
            ->assertOk()->assertJsonPath('slug', 'festa');

        $this->getJson('/api/categories')->assertOk()->assertJsonCount(1);
        $this->getJson("/api/categories/{$id}")->assertOk()->assertJsonPath('name', 'Festa');

        Product::factory()->create(['category_id' => $id]);
        $this->deleteJson("/api/categories/{$id}")
            ->assertUnprocessable()->assertJsonValidationErrors('category');

        $empty = Category::factory()->create();
        $this->deleteJson("/api/categories/{$empty->id}")->assertOk();
    }

    public function test_product_crud_promotional_rule_and_visibility(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $this->postJson('/api/products', [
            'name' => 'Vestido', 'price' => 100, 'promotional_price' => 120, 'category_id' => $category->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('promotional_price');

        $product = $this->postJson('/api/products', [
            'name' => 'Vestido', 'price' => 100, 'promotional_price' => 80, 'category_id' => $category->id,
        ])->assertCreated()
            ->assertJsonPath('active', true)
            ->assertJsonStructure(['category', 'images', 'variants'])
            ->json();

        $this->putJson("/api/products/{$product['id']}", ['price' => 70])
            ->assertUnprocessable()->assertJsonValidationErrors('promotional_price');

        $this->putJson("/api/products/{$product['id']}", ['active' => false])
            ->assertOk()->assertJsonPath('active', false);

        $this->getJson('/api/products')->assertOk()->assertJsonPath('total', 1);

        $this->app['auth']->forgetGuards();
        $this->getJson('/api/products')->assertOk()->assertJsonPath('total', 0);
        $this->getJson("/api/products/{$product['id']}")->assertNotFound();
    }

    public function test_product_filters(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['name' => 'Saia Jeans', 'category_id' => $category->id, 'featured' => true]);
        Product::factory()->create(['name' => 'Blusa', 'featured' => false]);

        $this->getJson('/api/products?search=saia')->assertJsonPath('total', 1);
        $this->getJson("/api/products?category_id={$category->id}")->assertJsonPath('total', 1);
        $this->getJson('/api/products?featured=false')->assertJsonPath('total', 1);
    }

    public function test_variant_and_image_crud(): void
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create();

        $variant = $this->postJson('/api/product-variants', [
            'product_id' => $product->id, 'size' => 'M', 'color' => 'Preto', 'stock' => 5,
        ])->assertCreated()->assertJsonPath('product.id', $product->id)->json();

        $this->postJson('/api/product-variants', [
            'product_id' => $product->id, 'size' => 'M', 'color' => 'Preto', 'stock' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('size');

        $this->putJson("/api/product-variants/{$variant['id']}", ['stock' => 9])
            ->assertOk()->assertJsonPath('stock', 9);
        $this->getJson("/api/product-variants?product_id={$product->id}")->assertJsonPath('total', 1);
        $this->deleteJson("/api/product-variants/{$variant['id']}")->assertOk();
        $this->assertSame(0, ProductVariant::count());

        $image = $this->postJson('/api/product-images', [
            'product_id' => $product->id, 'image_url' => 'https://exemplo.com/a.png',
        ])->assertCreated()->json();

        $this->putJson("/api/product-images/{$image['id']}", ['image_url' => 'https://exemplo.com/b.png'])
            ->assertOk()->assertJsonPath('image_url', 'https://exemplo.com/b.png');
        $this->getJson("/api/product-images/{$image['id']}")->assertOk()->assertJsonPath('product.id', $product->id);
        $this->deleteJson("/api/product-images/{$image['id']}")->assertOk();
        $this->assertSame(0, ProductImage::count());
    }

    public function test_user_crud_and_self_protection_rules(): void
    {
        $admin = $this->actingAsAdmin();

        $user = $this->postJson('/api/users', [
            'name' => 'Cliente', 'email' => 'c@c.com', 'password' => 'senha1234', 'role' => User::ROLE_CUSTOMER,
        ])->assertCreated()->assertJsonMissingPath('password')->json();

        $this->getJson('/api/users?search=c@c.com')->assertOk()->assertJsonPath('total', 1);
        $this->putJson("/api/users/{$user['id']}", ['name' => 'Novo'])->assertOk()->assertJsonPath('name', 'Novo');

        $this->putJson("/api/users/{$admin->id}", ['role' => User::ROLE_CUSTOMER])
            ->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->deleteJson("/api/users/{$admin->id}")
            ->assertUnprocessable()->assertJsonValidationErrors('user');

        $this->deleteJson("/api/users/{$user['id']}")->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $user['id']]);
    }
}
