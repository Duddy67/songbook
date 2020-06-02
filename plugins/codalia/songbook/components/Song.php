<?php namespace Codalia\SongBook\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Codalia\SongBook\Models\Song as SongItem;
use Codalia\SongBook\Models\Category;
use Codalia\SongBook\Models\Settings;
use Codalia\SongBook\Components\Songs;


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
                'default'     => '',
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
	$this->addCss(url('plugins/codalia/songbook/assets/css/breadcrumb.css'));

	if (!$this->song->canView()) {
	    return \Redirect::to(403);
	}

	if ($this->song->category->status != 'published') {
	    return \Redirect::to(404);
	}
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
	$song = $song->where('slug', $slug)
	             ->with(['categories' => function ($query) {
		            // Gets only published categories.
			    $query->where('status', 'published');
		     }]);

        /*if (!$this->checkEditor()) {
            $song = $song->isPublished();
	}*/

        try {
            $song = $song->firstOrFail();
        } catch (ModelNotFoundException $ex) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }

        // Add a "url" helper attribute for linking to the main category.
	$song->category->setUrl($this->categoryPage, $this->controller);

        /*
         * Add a "url" helper attribute for linking to each extra category
         */
        if ($song && $song->categories->count()) {
            $song->categories->each(function($category, $key) use($song) {
		$category->setUrl($this->categoryPage, $this->controller);
            });
	}

	// Builds the canonical link to the song.
	$song->path = Category::getCategoryPath($song->category);
	$canonical = '';

	foreach ($song->path as $key => $category) {
	    $canonical .= $category['slug'].'/';
	}

	$song->canonical = $canonical.$song->slug;

	if (Settings::get('show_breadcrumb')) {
	    $song->breadcrumb = $this->getBreadcrumb($song);
	    $song->prefix = Songs::getCategoryPrefix();
	}

        return $song;
    }

    /**
     * Returns the breadcrumb path to a given song.
     *
     * @param object $song
     *
     * @return array
     */
    public function getBreadcrumb($song)
    {
        // Removes the root part from the current url as well as the song slug 
        // in order to extract the category path.
        $songPath = preg_replace('#^'.url('/').'/#', '', $this->currentPageUrl());
        $categoryPath = preg_replace('#/'.$song->slug.'$#', '', $songPath);
	// Gets the category the song is in.
	$category = Category::where('path', $categoryPath)->first();
	$breadcrumb = [];

	// Builds the breadcrumb.
	if($category) {
	    $breadcrumb = Category::getCategoryPath($category);
	    // The last segment of the path is the song itself.
	    $breadcrumb[] = $song->attributes;
	}

	return $breadcrumb;
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
