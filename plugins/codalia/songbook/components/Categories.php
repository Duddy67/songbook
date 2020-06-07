<?php namespace Codalia\SongBook\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Codalia\SongBook\Models\Category as SongCategory;


class Categories extends ComponentBase
{

    /**
     * @var Collection A collection of categories to display
     */
    public $categories;

    /**
     * @var string Reference to the current category slug.
     */
    public $currentCategorySlug;


    public function componentDetails()
    {
        return [
            'name'        => 'codalia.songbook::lang.settings.category_title',
            'description' => 'codalia.songbook::lang.settings.category_description'
        ];
    }

    public function defineProperties()
    {
      return [
	    'slug' => [
                'title'       => 'codalia.songbook::lang.settings.category_slug',
                'description' => 'codalia.songbook::lang.settings.category_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'displayEmpty' => [
                'title'       => 'codalia.songbook::lang.settings.category_display_empty',
                'description' => 'codalia.songbook::lang.settings.category_display_empty_description',
                'type'        => 'checkbox',
                'default'     => 0,
            ],
      ];
    }


    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }


    public function onRun()
    {
	$this->currentCategorySlug = $this->page['currentCategorySlug'] = $this->property('slug');
	$this->categories = $this->page['categories'] = $this->loadCategories();
    }

    /**
     * Load all published categories or, depending on the <displayEmpty> option, only those that have songs
     * @return mixed
     */
    protected function loadCategories()
    {
        $categories = SongCategory::where('status', 'published')->with('songs_count')->getNested();

        if (!$this->property('displayEmpty')) {
            $iterator = function ($categories) use (&$iterator) {
                return $categories->reject(function ($category) use (&$iterator) {
                    if ($category->getNestedSongCount() == 0) {
                        return true;
                    }
                    if ($category->children) {
                        $category->children = $iterator($category->children);
                    }
                    return false;
                });
            };

            $categories = $iterator($categories);
        }

        /*
         * Add a "url" helper attribute for linking to each category
         */
        return $this->linkCategories($categories);
    }

    /**
     * Sets the URL on each category according to the defined category page
     * @return void
     */
    protected function linkCategories($categories)
    {
        return $categories->each(function ($category) {
            $category->setUrl($this->controller);

            if ($category->children) {
                $this->linkCategories($category->children);
            }
        });
    }
}
