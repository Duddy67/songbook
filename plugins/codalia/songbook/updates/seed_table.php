<?php namespace Codalia\SongBook\Updates;

use Seeder;
use Codalia\SongBook\Models\Song;
use Codalia\SongBook\Models\Category;

class SeedSongBookTables extends Seeder
{

    public $songs = [['title' => 'Come Together', 'slug' => 'come-together'],
		     ['title' => 'Heartbreak Hotel', 'slug' => 'heartbreak-hotel'],
		     ['title' => 'Hoochie Coochie Man', 'slug' => 'hoochie-coochie-man'],
		     ['title' => 'I\'m In The Mood', 'slug' => 'i-m-in-the-mood'],
		     ['title' => 'Johnny B. Good', 'slug' => 'johnny-b-good'],
		     ['title' => 'Jumping Jack Flash', 'slug' => 'jumping-jack-flash'],
		     ['title' => 'Layla', 'slug' => 'layla'],
		     ['title' => 'Light My Fire', 'slug' => 'light-my-fire'],
		     ['title' => 'Like A Rolling Stone', 'slug' => 'like-a-rolling-stone'],
		     ['title' => 'Message In The Bottle', 'slug' => 'message-in-the-bottle'],
		     ['title' => 'Miss You', 'slug' => 'miss-you'],
		     ['title' => 'My Baby Just Cares For Me', 'slug' => 'my-baby-just-cares-for-me'],
		     ['title' => 'My Funny Valentine', 'slug' => 'my-funny-valentine'],
		     ['title' => 'My Man', 'slug' => 'my-man'],
		     ['title' => 'Purple Haze', 'slug' => 'purple-haze'],
		     ['title' => 'Rolling Stone Blues', 'slug' => 'rolling-stone-blues'],
		     ['title' => 'Somethin\' Stupid', 'slug' => 'somethin-stupid'],
		     ['title' => 'Strangers In The Night', 'slug' => 'strangers-in-the-night'],
		     ['title' => 'The Sound Of Silence', 'slug' => 'the-sound-of-silence'],
		     ['title' => 'Whole Lotta Love', 'slug' => 'whole-lotta-love'],
		     ['title' => 'Yesterday', 'slug' => 'yesterday'],
		     ['title' => 'Ziggy Stardust', 'slug' => 'ziggy-stardust']
    ];

    public $categories = [['name' => 'Style', 'slug' => 'style'],
                          ['name' => 'Pop', 'slug' => 'pop'],
                          ['name' => 'Rock', 'slug' => 'rock'],
                          ['name' => 'Folk', 'slug' => 'folk'],
                          ['name' => 'Jazz', 'slug' => 'jazz'],
                          ['name' => 'Blues', 'slug' => 'blues'],
                          ['name' => 'Period', 'slug' => 'period'],
                          ['name' => 'Fifties', 'slug' => 'fifties'],
                          ['name' => 'Sixties', 'slug' => 'sixties'],
                          ['name' => 'Seventies', 'slug' => 'seventies'],
                          ['name' => 'Artist', 'slug' => 'artist'],
                          ['name' => 'The Beatles', 'slug' => 'the-beatles'],
                          ['name' => 'The Doors', 'slug' => 'the-doors'],
                          ['name' => 'The Police', 'slug' => 'the-police'],
                          ['name' => 'The Rolling Stones', 'slug' => 'the-rolling-stones'],
                          ['name' => 'David Bowie', 'slug' => 'david-bowie'],
                          ['name' => 'Jimi Hendrix', 'slug' => 'jimi-hendrix'],
                          ['name' => 'John Lee Hooker', 'slug' => 'john-lee-hooker'],
                          ['name' => 'Billie Holyday', 'slug' => 'billie-holyday'],
                          ['name' => 'Bob Dylan', 'slug' => 'bob-dylan'],
                          ['name' => 'Elvis Presley', 'slug' => 'elvis-presley'],
                          ['name' => 'Chuck Berry', 'slug' => 'chuck-berry'],
                          ['name' => 'Pink Floyd', 'slug' => 'pink-floyd'],
                          ['name' => 'Frank Sinatra', 'slug' => 'frank-sinatra'],
                          ['name' => 'Muddy Waters', 'slug' => 'muddy-waters'],
                          ['name' => 'Eric Clapton', 'slug' => 'eric-clapton'],
                          ['name' => 'Simon & Garfunkel', 'slug' => 'simon-and-garfunkel'],
                          ['name' => 'Peter Paul and Mary', 'slug' => 'peter-paul-and-mary'],
                          ['name' => 'Nina Simone', 'slug' => 'nina-simone'],
                          ['name' => 'Led Zeppelin', 'slug' => 'led-zeppelin'],
                          ['name' => 'Chet Baker', 'slug' => 'chet-baker']
    ];


    public function run()
    {
      foreach ($this->songs as $key => $song) {
	$order = $key + 1;

	Song::create(['title' => $song['title'], 'slug' => $song['slug'], 
		     'lyrics' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 
		     'status' => 'published', 'created_by' => 1, 'updated_by' => 1, 'sort_order' => $order,
		     'created_at' => '2020-03-16 04:35:00', 'published_up' => '2020-03-16 04:35:00']);
      }

      foreach ($this->categories as $category) {
	Category::create(['name' => $category['name'], 'slug' => $category['slug'], 
		     'description' => '<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 
		     'status' => 'published']);
      }
    }
}

