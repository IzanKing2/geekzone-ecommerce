<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

trait CreateBaseData
{
    // ——————————————————————————————————————————————————————————————————————————
    // CREACIÓN DE DATOS DE PRUEBA
    // ——————————————————————————————————————————————————————————————————————————
    protected function createCategoriesForTest(): array
    {
        $marvel = Category::create([
            'name'      => 'Marvel',
            'description' => 'Productos de Marvel para testing',
            'image_url'  => 'img/test/marvel.jpg',
        ]);

        $futbol = Category::create([
            'name'      => 'Fútbol',
            'description' => 'Productos de Fútbol para testing',
            'image_url'  => 'img/test/futbol.jpg',
        ]);

        return [$marvel, $futbol];
    }

    protected function createProductsForTest(int $categoryId): array
    {
        $product1 = Product::create([
            'name'       => 'Figura Spider-Man Test',
            'description'  => 'Figura de acción de Spider-Man para tests',
            'price'       => 29.99,
            'stock'        => 10,
            'image_url'   => 'img/test/spiderman.jpg',
            'category_id' => $categoryId,
        ]);

        $product2 = Product::create([
            'name'       => 'Camiseta Iron Man Test',
            'description'  => 'Camiseta oficial de Iron Man para tests',
            'price'       => 19.99,
            'stock'        => 5,
            'image_url'   => 'img/test/ironman.jpg',
            'category_id' => $categoryId,
        ]);

        $product3 = Product::create([
            'name'       => 'Taza Thor Test',
            'description'  => 'Taza de Thor para tests',
            'price'       => 12.50,
            'stock'        => 20,
            'image_url'   => 'img/test/thor.jpg',
            'category_id' => $categoryId,
        ]);

        return [$product1, $product2, $product3];
    }

    // ——————————————————————————————————————————————————————————————————————————
    // CREACIÓN DE USUARIOS
    // ——————————————————————————————————————————————————————————————————————————

    protected function createNormalUser(): User
    {
        return User::create([
            'name'     => 'Usuario Test',
            'email'    => 'test@geekzone.com',
            'password' => 'password123',
            'role'      => 'user',
        ]);
    }

    protected function createAdminUser(): User
    {
        return User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin-test@geekzone.com',
            'password' => 'admin123',
            'role'      => 'admin',
        ]);
    }

    // ——————————————————————————————————————————————————————————————————————————
    // MÉTODOS DE AUTENTICACIÓN PARA TESTS
    // ——————————————————————————————————————————————————————————————————————————

    protected function getTokenForUser(string $email, string $password): string
    {
        $response = $this->postJson('/api/login', [
            'email'    => $email,
            'password' => $password,
        ]);

        return $response->json('token');
    }

    protected function getHeadersWithToken(string $token): array
    {
        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    protected function loginAsUser(): array
    {
        $this->createNormalUser();
        $token = $this->getTokenForUser('test@geekzone.com', 'password123');
        return $this->getHeadersWithToken($token);
    }

    protected function loginAsAdmin(): array
    {
        $this->createAdminUser();
        $token = $this->getTokenForUser('admin-test@geekzone.com', 'admin123');
        return $this->getHeadersWithToken($token);
    }
}
