<?php namespace Codalia\SongBook\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Codalia\SongBook\Models\Song as SongItem;

class Song extends ComponentBase
{
    /**
     * @var Codalia\SongBook\Models\Song The song model used for display.
     */
    public $song;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $categoryPage;


    public function componentDetails()
    {
        return [
            'name'        => 'codalia.songbook::lang.settings.song_title',
            'description' => 'codalia.songbook::lang.settings.song_description'
        ];
    }

    public function defineProperties()
    {
	  return [
            'slug' => [
                'title'       => 'codalia.songbook::lang.settings.song_slug',
                'description' => 'codalia.songbook::lang.settings.song_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'categoryPage' => [
                'title'       => 'codalia.songbook::lang.settings.song_category',
                'description' => 'codalia.songbook::lang.settings.song_category_description',
                'type'        => 'dropdown',
                'default'     => 'songbook/category',
            ],
        ];
    }


    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->song = $this->page['song'] = $this->loadSong();
    }

    public function onRender()
    {
        if (empty($this->song)) {
            $this->song = $this->page['song'] = $this->loadSong();
        }
    }

    protected function loadSong()
    {
        $slug = $this->property('slug');

        $song = new SongItem;

	// Retrieves the song on the basis of its slug.
	$song = $song->where('slug', $slug);

        /*if (!$this->checkEditor()) {
            $song = $song->isPublished();
	}*/

        try {
            $song = $song->firstOrFail();
        } catch (ModelNotFoundException $ex) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }

        /*
         * Add a "url" helper attribute for linking to each category
         */
        if ($song && $song->categories->count()) {
            $song->categories->each(function($category) {
                $category->setUrl($this->categoryPage, $this->controller);
            });
        }

        return $song;
    }

    public function previousSong()
    {
        return $this->getSongSibling(-1);
    }

    public function nextSong()
    {
        return $this->getSongSibling(1);
    }

    protected function getSongSibling($direction = 1)
    {
        if (!$this->song) {
            return;
        }

        $method = $direction === -1 ? 'previousSong' : 'nextSong';

        if (!$song = $this->song->$method()) {
            return;
        }

        $songPage = $this->getPage()->getBaseFileName();

        $song->setUrl($songPage, $this->controller);

        $song->categories->each(function($category) {
            $category->setUrl($this->categoryPage, $this->controller);
        });

        return $song;
    }
}
