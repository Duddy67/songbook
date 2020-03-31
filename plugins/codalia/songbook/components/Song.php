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

        /*$song = $song->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $song->transWhere('slug', $slug)
            : $song->where('slug', $slug);

        if (!$this->checkEditor()) {
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




}
