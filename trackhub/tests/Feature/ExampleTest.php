<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_redirects_unauthenticated_users_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
    }

    public function test_users_can_authenticate_using_login_form(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_gps_reading_can_be_stored_publicly(): void
    {
        $response = $this->postJson('/api/gps-readings', [
            'device_id' => 'esp32-gps-01',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'altitude_m' => 12.34,
            'speed_kmph' => 4.56,
            'satellites' => 8,
            'hdop' => 1.25,
            'raw_nmea' => '$GPGGA,123519,4807.038,N,01131.000,E',
            'offline' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.device_id', 'esp32-gps-01')
            ->assertJsonPath('data.offline', true);

        $this->assertDatabaseHas('gps_readings', [
            'device_id' => 'esp32-gps-01',
            'offline' => true,
        ]);
    }

    public function test_gps_reading_index_route_is_protected(): void
    {
        $response = $this->getJson('/api/gps-readings');

        $response->assertStatus(401);
    }

    public function test_gps_reading_accepts_esp32_speed_payload_and_exposes_coordinates_when_authenticated(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/gps-readings', [
            'latitude' => -7.2574719,
            'longitude' => 112.7520883,
            'speed' => 12.5,
            'satellites' => 10,
            'hdop' => 0.9,
        ])->assertCreated();

        $response = $this->actingAs($user)->getJson('/api/gps-readings');

        $response
            ->assertOk()
            ->assertJsonPath('latest.speed_kmph', 12.5)
            ->assertJsonPath('coordinates.0', -7.2574719)
            ->assertJsonPath('coordinates.1', 112.7520883);
    }
}

