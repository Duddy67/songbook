<?php namespace Codalia\SongBook\Components;

use Lang;
use BackendAuth;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Codalia\SongBook\Models\Song;
use Codalia\SongBook\Models\Category as SongCategory;
use Codalia\SongBook\Models\Settings as SongSettings;
use Auth;


class Songs extends ComponentBase
{
    /**
     * A collection of songs to display
     *
     * @var Collection
     */
    public $songs;

    /**
     * Parameter to use for the page number
     *
     * @var string
     */
    public $pageParam;

    /**
     * If the song list should be filtered by a category, the model to use
     *
     * @var Model
     */
    public $category;

    /**
     * Message to display when there are no messages
     *
     * @var string
     */
    public $noSongsMessage;

    /**
     * Reference to the page name for linking to songs
     *
     * @var string
     */
    public $songPage;

    /**
     * Reference to the page name for linking to categories
     *
     * @var string
     */
    public $categoryPage;

    /**
     * If the song list should be ordered by another attribute
     *
     * @var string
     */
    public $sortOrder;


    public function componentDetails()
    {
        return [
            'name'        => 'codalia.songbook::lang.settings.songs_title',
            'description' => 'codalia.songbook::lang.settings.songs_description'
        ];
    }

    public function defineProperties()
    {
	return [
            'pageNumber' => [
                'title'       => 'codalia.songbook::lang.settings.songs_pagination',
                'description' => 'codalia.songbook::lang.settings.songs_pagination_description',
                'type'        => 'string',
                'default'     => '{{ :page }}'
            ],
            'categoryFilter' => [
                'title'       => 'codalia.songbook::lang.settings.songs_filter',
                'description' => 'codalia.songbook::lang.settings.songs_filter_description',
                'type'        => 'string',
                'default'     => '{{ :slug }}',
            ],
            'songsPerPage' => [
                'title'             => 'codalia.songbook::lang.settings.songs_per_page',
                'default'           => 5,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'codalia.songbook::lang.settings.songs_per_page_validation',
                'showExternalParam' => false
            ],
            'noSongsMessage' => [
                'title'             => 'codalia.songbook::lang.settings.songs_no_songs',
                'description'       => 'codalia.songbook::lang.settings.songs_no_songs_description',
                'type'              => 'string',
                'default'           => Lang::get('codalia.songbook::lang.settings.songs_no_songs_default'),
                'showExternalParam' => false
            ],
            'sortOrder' => [
                'title'       => 'codalia.songbook::lang.settings.songs_order',
                'description' => 'codalia.songbook::lang.settings.songs_order_description',
                'type'        => 'dropdown',
                'default'     => 'published_at desc'
            ],
            'categoryPage' => [
                'title'       => 'codalia.songbook::lang.settings.songs_category',
                'description' => 'codalia.songbook::lang.settings.songs_category_description',
                'type'        => 'dropdown',
                'default'     => 'songbook/category',
                'group'       => 'codalia.songbook::lang.settings.group_links'
            ],
            'songPage' => [
                'title'       => 'codalia.songbook::lang.settings.songs_song',
                'description' => 'codalia.songbook::lang.settings.songs_song_description',
                'type'        => 'dropdown',
                'default'     => 'songbook/song',
                'group'       => 'codalia.songbook::lang.settings.group_links'
            ],
            'exceptSong' => [
                'title'             => 'codalia.songbook::lang.settings.songs_except_song',
                'description'       => 'codalia.songbook::lang.settings.songs_except_song_description',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'codalia.songbook::lang.settings.songs_except_song_validation',
                'default'           => '',
                'group'             => 'codalia.songbook::lang.settings.group_exceptions'
            ],
            'exceptCategories' => [
                'title'             => 'codalia.songbook::lang.settings.songs_except_categories',
                'description'       => 'codalia.songbook::lang.settings.songs_except_categories_description',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'codalia.songbook::lang.settings.songs_except_categories_validation',
                'default'           => '',
                'group'             => 'codalia.songbook::lang.settings.group_exceptions'
            ]
        ];
    }


    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getSongPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getSortOrderOptions()
    {
        $options = Song::$allowedSortingOptions;

        foreach ($options as $key => $value) {
            $options[$key] = Lang::get($value);
        }

        return $options;
    }

    public static function getUserGroupIds()
    {
        $ids = [];

	if (Auth::check()) {
	    $userGroups = Auth::getUser()->getGroups();

	    foreach ($userGroups as $userGroup) {
	        $ids[] = $userGroup->id;
	    }
	}

	return $ids;
    }

    public function onRun()
    {
        $this->prepareVars();
        $this->category = $this->page['category'] = $this->loadCategory();
        $this->songs = $this->page['songs'] = $this->listSongs();
	$this->addCss(url('plugins/codalia/songbook/assets/css/breadcrumb.css'));

        /*
         * If the page number is not valid, redirect
         */
        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->songs->lastPage()) && $currentPage > 1) {
                return \Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
            }
        }
    }

    protected function prepareVars()
    {
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->noSongsMessage = $this->page['noSongsMessage'] = $this->property('noSongsMessage');

        /*
         * Page links
         */
        $this->songPage = $this->page['songPage'] = $this->property('songPage');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
    }

    protected function listSongs()
    {
        $category = $this->category ? $this->category->id : null;

        /*
         * List all the songs, eager load their categories
         */
        //$isPublished = !$this->checkEditor();
	$isPublished = true;

	$songs = Song::whereHas('category', function ($query) {
	        // Songs must have their main category published.
		$query->where('status', 'published');
	})->where(function($query) { 
	        // Gets songs which match the groups of the current user.
		$query->whereIn('access_id', self::getUserGroupIds()) 
		      ->orWhereNull('access_id');
        })->with(['categories' => function ($query) {
	        // Gets published categories only.
		$query->where('status', 'published');
	}])->listFrontEnd([
            'page'             => $this->property('pageNumber'),
            'sort'             => $this->property('sortOrder'),
            'perPage'          => $this->property('songsPerPage'),
            'search'           => trim(input('search')),
            'category'         => $category,
            'published'        => $isPublished,
            'exceptSong'       => is_array($this->property('exceptSong'))
                ? $this->property('exceptSong')
                : preg_split('/,\s*/', $this->property('exceptSong'), -1, PREG_SPLIT_NO_EMPTY),
            'exceptCategories' => is_array($this->property('exceptCategories'))
                ? $this->property('exceptCategories')
                : preg_split('/,\s*/', $this->property('exceptCategories'), -1, PREG_SPLIT_NO_EMPTY),
        ]);

        /*
         * Add a "url" helper attribute for linking to each song and category
         */
        $songs->each(function($song, $key) {
	    $song->setUrl($this->songPage, $this->controller, $this->category);

	    $song->categories->each(function($category, $key) {
		$category->setUrl($this->categoryPage, $this->controller);
	    });
        });

        return $songs;
    }

    protected function loadCategory()
    {
        if (!$slug = $this->property('categoryFilter')) {
            return null;
        }

        $category = new SongCategory;

        /*$category = $category->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $category->transWhere('slug', $slug)
	    : $category->where('slug', $slug);*/

        $category = $category->where('slug', $slug);
        $category = $category->first();

        return $category ?: null;
    }

    protected function checkEditor()
    {
        $backendUser = BackendAuth::getUser();

        return $backendUser && $backendUser->hasAccess('rainlab.blog.access_posts') && SongSettings::get('show_all_posts', true);
    }
}
