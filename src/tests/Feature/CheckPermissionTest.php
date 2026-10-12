<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckPermission;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CheckPermissionTest extends TestCase
{
    public function test_it_redirects_with_message_when_user_has_no_permission(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('route_name');
            $table->string('section')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_sidebar')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Menu::create([
            'name' => 'Restricted Menu',
            'route_name' => 'restricted.menu',
            'section' => 'Test',
            'icon' => 'bi bi-box',
            'sort_order' => 1,
            'is_sidebar' => true,
        ]);

        $user = new class extends User {
            public function hasMenuPermission(string $routeName, string $permission = 'view'): bool
            {
                return false;
            }
        };

        $user->forceFill([
            'id' => 1,
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);

        $this->app['router']->get('/dashboard', function () {
            return 'dashboard';
        })->name('dashboard');

        $this->actingAs($user);

        $request = Request::create('/restricted', 'GET');
        $request->setRouteResolver(function () {
            $route = new Route('GET', '/restricted', ['uses' => function () {
                return 'ok';
            }]);
            $route->name('restricted.menu');

            return $route;
        });

        $middleware = new CheckPermission();
        $response = $middleware->handle($request, function ($request) {
            return response('ok');
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/dashboard', $response->getTargetUrl());
        $this->assertEquals('Anda tidak memiliki hak akses untuk menu ini.', session('error'));
    }
}
