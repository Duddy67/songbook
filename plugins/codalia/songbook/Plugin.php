<?php namespace Codalia\SongBook;

use Backend;
use System\Classes\PluginBase;
use Backend\Models\User as BackendUserModel;
use RainLab\User\Models\User as UserModel;
use RainLab\User\Controllers\Users as UsersController;
use RainLab\User\Models\UserGroup;
use Codalia\SongBook\Models\Song;
use Backend\FormWidgets\Relation;
use Event;
use Db;

/**
 * songbook Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Song Book',
            'description' => 'A simple note book used for songs.',
            'author'      => 'codalia',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
	BackendUserModel::extend(function ($model) {
	    $model->hasMany['songs'] = ['Codalia\SongBook\Models\Song', 'key' => 'created_by'];
	});

	// Ensures first that the RainLab User plugin is installed and activated.
	if (\System\Classes\PluginManager::instance()->exists('RainLab.User')) {
	    UserGroup::extend(function ($model) {
		$model->hasMany['songs'] = ['Codalia\SongBook\Models\Song', 'key' => 'access_id'];
	    });
	}

	// Extends the partial files used for the relation type fields.
	Relation::extend(function ($widget) {
	    $widget->addViewPath(['$/codalia/songbook/models/song']);
	});

	\Cms\Controllers\Index::extend(function ($controller) {
	    $controller->bindEvent('template.processSettingsBeforeSave', function ($dataHolder) {
	        $data = post();  
		// Ensures the page file names for categories fit the correct pattern.
		if ($data['templateType'] == 'page' &&
		    in_array('songList', $data['component_names']) &&
		    !preg_match('#^category-level-[0-9]+\.htm$#', $data['fileName'])) {
		    throw new \ApplicationException(\Lang::get('codalia.songbook::lang.settings.invalid_file_name'));
		}
	    });
	});
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Codalia\SongBook\Components\Song' => 'song',
            'Codalia\SongBook\Components\Songs' => 'songList',
            'Codalia\SongBook\Components\Categories' => 'songCategories',
            'Codalia\SongBook\Components\Featured' => 'featuredSongs',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'codalia.songbook.manage_settings' => [
                'tab' => 'codalia.songbook::lang.songbook.tab',
                'label' => 'codalia.songbook::lang.songbook.manage_settings',
		'order' => 200
	      ],
            'codalia.songbook.access_songs' => [
                'tab' => 'codalia.songbook::lang.songbook.tab',
                'label' => 'codalia.songbook::lang.songbook.access_songs',
		'order' => 201
            ],
            'codalia.songbook.access_categories' => [
                'tab' => 'codalia.songbook::lang.songbook.tab',
                'label' => 'codalia.songbook::lang.songbook.access_categories',
		'order' => 202
            ],
            'codalia.songbook.access_publish' => [
                'tab' => 'codalia.songbook::lang.songbook.tab',
                'label' => 'codalia.songbook::lang.songbook.access_publish'
            ],
            'codalia.songbook.access_delete' => [
                'tab' => 'codalia.songbook::lang.songbook.tab',
                'label' => 'codalia.songbook::lang.songbook.access_delete'
            ],
            'codalia.songbook.access_other_songs' => [
                'tab' => 'codalia.songbook::lang.songbook.tab',
                'label' => 'codalia.songbook::lang.songbook.access_other_songs'
            ],
            'codalia.songbook.access_check_in' => [
                'tab' => 'codalia.songbook::lang.songbook.tab',
                'label' => 'codalia.songbook::lang.songbook.access_check_in'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'songbook' => [
                'label'       => 'Song Book',
                'url'         => Backend::url('codalia/songbook/songs'),
                'icon'        => 'icon-music',
                'permissions' => ['codalia.songbook.*'],
                'order'       => 500,
	    'sideMenu' => [
		'new_song' => [
		    'label'       => 'codalia.songbook::lang.songs.new_song',
		    'icon'        => 'icon-plus',
		    'url'         => Backend::url('codalia/songbook/songs/create'),
		    'permissions' => ['codalia.songbook.access_songs']
		],
		'songs' => [
		    'label'       => 'codalia.songbook::lang.songbook.songs',
		    'icon'        => 'icon-copy',
		    'url'         => Backend::url('codalia/songbook/songs'),
		    'permissions' => ['codalia.songbook.access_songs']
		],
		'categories' => [
		    'label'       => 'codalia.songbook::lang.songbook.categories',
		    'icon'        => 'icon-sitemap',
		    'url'         => Backend::url('codalia/songbook/categories'),
		    'permissions' => ['codalia.songbook.access_categories']
		]
	      ]
            ],
        ];
    }


    public function registerSettings()
    {
	return [
	    'songbook' => [
		'label'       => 'Song Book',
		'description' => 'Manage available user countries and states.',
		'category'    => 'SONG BOOK',
		'icon'        => 'icon-music',
		'class' => 'Codalia\SongBook\Models\Settings',
		'order'       => 500,
		'keywords'    => 'geography place placement',
		'permissions' => ['codalia.songbook.manage_settings']
	    ]
	];
    }
}
