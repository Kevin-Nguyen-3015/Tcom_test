<?php
namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookingTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * Test single day booking.
     */
    public function test_single_day_booking()
    {
        $response = $this->postJson('/api/bookings', [
            'name' => 'Test Booking',
            'space_id' => 1,
            'start_time' => '2024-09-15 11:00:00',
            'end_time' => '2024-09-15 12:00:00',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'space_id',
                        'start_time',
                        'end_time',
                        'date_booking',
                    ]
                ]
            ]);
    }

    /**
     * Test multi-day booking.
     */
    public function test_multi_day_booking()
    {
        $response = $this->postJson('/api/bookings', [
            'name' => 'Multi-day Booking',
            'space_id' => 2,
            'start_time' => '2024-09-15 18:00:00',
            'end_time' => '2024-09-17 09:00:00',
        ]);

        $response->assertStatus(201)
            ->assertJsonCount(3, 'data') // Ensure 3 bookings for 3 days
            ->assertJsonFragment([
                'date_booking' => '20240915',
                'start_time' => '18:00',
                'end_time' => '24:00',
            ])
            ->assertJsonFragment([
                'date_booking' => '20240916',
                'start_time' => '00:00',
                'end_time' => '24:00',
            ])
            ->assertJsonFragment([
                'date_booking' => '20240917',
                'start_time' => '00:00',
                'end_time' => '09:00',
            ]);
    }

    /**
     * Test invalid booking with duration less than one hour.
     */
    public function test_invalid_booking_duration_less_than_one_hour()
    {
        $response = $this->postJson('/api/bookings', [
            'name' => 'Short Booking',
            'space_id' => 1,
            'start_time' => '2024-09-15 10:00:00',
            'end_time' => '2024-09-15 10:30:00',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Rent time should be at least one hour'
            ]);
    }

    /**
     * Test invalid booking with overlapping time.
     */
    public function test_invalid_booking_overlapping()
    {
        $response = $this->postJson('/api/bookings', [
            'name' => 'Overlap Booking',
            'space_id' => 1,
            'start_time' => '2024-09-15 09:30:00',
            'end_time' => '2024-09-15 10:30:00',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Time slot is already booked'
            ]);
    }

    /**
     * Test rollback on overlapping booking in multi-day booking.
     */
    public function test_rollback_on_overlapping_multi_day_booking()
    {
        $response = $this->postJson('/api/bookings', [
            'name' => 'Multi-day Overlap',
            'space_id' => 3,
            'start_time' => '2024-09-15 18:00:00',
            'end_time' => '2024-09-17 09:00:00',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Time slot is already booked'
            ]);

        // Verify no bookings were created due to the rollback
        $this->assertDatabaseMissing('bookings', [
            'name' => 'Multi-day Overlap',
            'space_id' => 3,
            'date_booking' => '20240915'
        ]);
        $this->assertDatabaseMissing('bookings', [
            'name' => 'Multi-day Overlap',
            'space_id' => 3,
            'date_booking' => '20240916'
        ]);
        $this->assertDatabaseMissing('bookings', [
            'name' => 'Multi-day Overlap',
            'space_id' => 3,
            'date_booking' => '20240917'
        ]);
    }
}
