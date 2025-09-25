<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Event::create([
            'title' => 'Annual Cultural Festival',
            'description' => 'Join us for an exciting cultural festival featuring traditional music, dance performances, and delicious food from various cultures.',
            'event_date' => now()->addDays(15)->toDateString(),
            'event_time' => '18:00:00',
            'location' => 'Gurudakshina Hall Main Auditorium',
            'is_active' => true
        ]);

        Event::create([
            'title' => 'Business Networking Meetup',
            'description' => 'Connect with local entrepreneurs and business leaders in this exclusive networking event. Perfect for expanding your professional network.',
            'event_date' => now()->addDays(22)->toDateString(),
            'event_time' => '10:00:00',
            'location' => 'Gurudakshina Hall Conference Room',
            'is_active' => true
        ]);

        Event::create([
            'title' => 'Educational Seminar: Future of Technology',
            'description' => 'Learn about emerging technologies and their impact on education and business. Featuring expert speakers from leading tech companies.',
            'event_date' => now()->addDays(30)->toDateString(),
            'event_time' => '14:00:00',
            'location' => 'Gurudakshina Hall Seminar Hall',
            'is_active' => true
        ]);

        Event::create([
            'title' => 'Charity Fundraiser Dinner',
            'description' => 'Support local charities while enjoying a delicious dinner and entertainment. All proceeds go to helping underprivileged children.',
            'event_date' => now()->addDays(45)->toDateString(),
            'event_time' => '19:30:00',
            'location' => 'Gurudakshina Hall Grand Ballroom',
            'is_active' => true
        ]);
    }
}
