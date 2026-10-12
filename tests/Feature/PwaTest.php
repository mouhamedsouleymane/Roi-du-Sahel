<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_is_served_and_installable(): void
    {
        $response = $this->get('/manifest.webmanifest');

        $response->assertOk();
        $manifest = $response->json();

        $this->assertSame('Les Rois du Sahel', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/', $manifest['scope']);

        // Installabilité : la start_url doit être publique (pas de 302 vers /login).
        $this->assertStringStartsWith('/?', $manifest['start_url']);
        $this->get('/')->assertOk();

        $sizes = collect($manifest['icons'])->pluck('sizes')->all();
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes);

        foreach (collect($manifest['icons'])->pluck('src') as $src) {
            $this->assertTrue(File::exists(public_path($src)), "Icône PWA manquante : {$src}");
        }
    }

    public function test_service_worker_is_served(): void
    {
        $this->get('/sw.js')
            ->assertOk()
            ->assertSee('rds-pwa-v2', false)
            ->assertSee('/offline', false);
    }

    public function test_offline_page_is_served(): void
    {
        $this->get('/offline')
            ->assertOk()
            ->assertSee('HORS LIGNE', false);
    }

    public function test_install_assets_exist(): void
    {
        foreach (['js/pwa-install.js', 'icons/icon-192x192.png', 'icons/maskable-512x512.png'] as $asset) {
            $this->assertTrue(File::exists(public_path($asset)), "Asset PWA manquant : {$asset}");
        }

        // Le mode diagnostic ?pwa-debug=1 doit exister pour isoler les pannes en production.
        $this->assertStringContainsString('pwa-debug', File::get(public_path('js/pwa-install.js')));
    }

    public function test_public_pages_propose_installation(): void
    {
        $this->get('/')->assertOk()->assertSee('manifest.webmanifest', false)->assertSee('pwa-install-banner', false);
        $this->get('/login')->assertOk()->assertSee('manifest.webmanifest', false)->assertSee('pwa-install-banner', false);
    }

    public function test_authenticated_layout_proposes_installation(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('pwa-install-banner', false);
    }
}
