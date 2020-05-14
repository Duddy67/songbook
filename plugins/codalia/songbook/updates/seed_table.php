<?php namespace Codalia\SongBook\Updates;

use Seeder;
use Codalia\SongBook\Models\Song;
use Codalia\SongBook\Models\Category;

class SeedSongBookTables extends Seeder
{

    public $songs = [['title' => 'Come Together', 'slug' => 'come-together', 'category_id' => 12],
		     ['title' => 'Heartbreak Hotel', 'slug' => 'heartbreak-hotel', 'category_id' => 21],
		     ['title' => 'Hoochie Coochie Man', 'slug' => 'hoochie-coochie-man', 'category_id' => 25],
		     ['title' => 'I\'m In The Mood', 'slug' => 'i-m-in-the-mood', 'category_id' => 18],
		     ['title' => 'Johnny B. Good', 'slug' => 'johnny-b-good', 'category_id' => 22],
		     ['title' => 'Jumping Jack Flash', 'slug' => 'jumping-jack-flash', 'category_id' => 15],
		     ['title' => 'Layla', 'slug' => 'layla', 'category_id' => 26],
		     ['title' => 'Light My Fire', 'slug' => 'light-my-fire', 'category_id' => 13],
		     ['title' => 'Like A Rolling Stone', 'slug' => 'like-a-rolling-stone', 'category_id' => 20],
		     ['title' => 'Message In The Bottle', 'slug' => 'message-in-the-bottle', 'category_id' => 14],
		     ['title' => 'Miss You', 'slug' => 'miss-you', 'category_id' => 15],
		     ['title' => 'My Baby Just Cares For Me', 'slug' => 'my-baby-just-cares-for-me', 'category_id' => 29],
		     ['title' => 'My Funny Valentine', 'slug' => 'my-funny-valentine', 'category_id' => 31],
		     ['title' => 'My Man', 'slug' => 'my-man', 'category_id' => 19],
		     ['title' => 'Purple Haze', 'slug' => 'purple-haze', 'category_id' => 17],
		     ['title' => 'Rolling Stone Blues', 'slug' => 'rolling-stone-blues', 'category_id' => 25],
		     ['title' => 'Somethin\' Stupid', 'slug' => 'somethin-stupid', 'category_id' => 24],
		     ['title' => 'Strangers In The Night', 'slug' => 'strangers-in-the-night', 'category_id' => 24],
		     ['title' => 'The Sound Of Silence', 'slug' => 'the-sound-of-silence', 'category_id' => 27],
		     ['title' => 'Whole Lotta Love', 'slug' => 'whole-lotta-love', 'category_id' => 30],
		     ['title' => 'Yesterday', 'slug' => 'yesterday', 'category_id' => 12],
		     ['title' => 'Ziggy Stardust', 'slug' => 'ziggy-stardust', 'category_id' => 16]
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
	$day = (string)$order;
	if ($order < 10) {
	  $day = '0'.$order;
	}

	Song::create(['title' => $song['title'], 'slug' => $song['slug'], 'category_id' => $song['category_id'],
		     'lyrics' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 
		     'status' => 'published', 'created_by' => 1, 'updated_by' => 1, 
		     'created_at' => '2020-03-'.$day.' 04:35:00', 'published_up' => '2020-04-'.$day.' 17:08:54']);
      }

      foreach ($this->categories as $category) {
	Category::create(['name' => $category['name'], 'slug' => $category['slug'], 
		     'description' => '<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 
		     'status' => 'published']);
      }
    }
}

