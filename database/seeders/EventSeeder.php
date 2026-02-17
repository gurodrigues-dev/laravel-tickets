<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'name' => 'Obon',
                'description' => 'The festival is based on a legend about a Buddhist monk called Mogallana. The story goes that Mogallana could see into the afterlife, and saved his deceased mother from going to hell by giving offerings to Buddhist monks. Having gained redemption for his mother, he danced in celebration, joined by others in a large circle. This dance is known as the Bon Odori dance.',
                'event_date' => '2027-08-13T13:00:00Z',
                'total_tickets' => 200,
                'available_tickets' => 200,
                'version' => 1,
            ],
            [
                'name' => 'Carnival',
                'description' => 'This festival is known for being a time of great indulgence before Lent, with drinking, overeating, and various other activities of indulgence being performed. On the final day of the season, Shrove Tuesday, many traditional Christians make a special point of self-examination and repentance.',
                'event_date' => '2013-03-03T10:00:00Z',
                'total_tickets' => 5,
                'available_tickets' => 5,
                'version' => 1,
            ],
            [
                'name' => 'Swiss Yodelling Festival',
                'description' => 'Natural yodelling exists all over the world, especially in mountainous regions where the technique was used to communicate over long distances. Choir singing developed in the 19th century.',
                'event_date' => '2026-06-17T14:00:00Z',
                'total_tickets' => 1,
                'available_tickets' => 1,
                'version' => 1,
            ],
            [
                'name' => 'Tanabata Matsuri',
                'description' => 'This event celebrates the meeting of the deities Orihime and Hikoboshi, represented by the stars Vega and Altair. According to legend, they are allowed to meet once a year.',
                'event_date' => '2007-07-07T13:00:00Z',
                'total_tickets' => 10,
                'available_tickets' => 10,
                'version' => 1,
            ],
            [
                'name' => 'Sechseläuten',
                'description' => 'This Zurich spring custom got its name from the medieval custom of ringing a bell of the Grossmünster every evening at six o\'clock to proclaim the end of the working day during the summer semester.',
                'event_date' => '2047-04-21T09:00:00Z',
                'total_tickets' => 0,
                'available_tickets' => 0,
                'version' => 1,
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
