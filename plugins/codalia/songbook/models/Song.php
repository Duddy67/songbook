<?php namespace Codalia\SongBook\Models;

use Lang;
use Html;
use Model;
use Auth;
use BackendAuth;
use October\Rain\Support\Str;
use October\Rain\Database\Traits\Validation;
use Carbon\Carbon;
use Codalia\SongBook\Models\Settings;
use Db;


/**
 * Song Model
 */
class Song extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'codalia_songbook_songs';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = ['title' => 'required'];

    /**
     * @var array Custom validation messages
     */
    public $customMessages = [
        'title.required' => 'codalia.songbook::lang.messages.required_field'
      ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = ['summary'];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'published_up',
        'published_down'
    ];

    /**
     * The attributes on which the song list can be ordered.
     * @var array
     */
    public static $allowedSortingOptions = [
        'title asc'         => 'codalia.songbook::lang.sorting.title_asc',
        'title desc'        => 'codalia.songbook::lang.sorting.title_desc',
        'created_at asc'    => 'codalia.songbook::lang.sorting.created_asc',
        'created_at desc'   => 'codalia.songbook::lang.sorting.created_desc',
        'updated_at asc'    => 'codalia.songbook::lang.sorting.updated_asc',
        'updated_at desc'   => 'codalia.songbook::lang.sorting.updated_desc',
        'published_up asc'  => 'codalia.songbook::lang.sorting.published_asc',
        'published_up desc' => 'codalia.songbook::lang.sorting.published_desc',
        'random'            => 'codalia.songbook::lang.sorting.random'
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [
    ];
    public $hasMany = [];
    public $belongsTo = [
        'user' => ['Backend\Models\User', 'key' => 'created_by'],
        'usergroup' => ['RainLab\User\Models\UserGroup', 'key' => 'access_id'],
        'category' => ['Codalia\SongBook\Models\Category'],
    ];
    public $belongsToMany = [
        'categories' => [
            'Codalia\SongBook\Models\Category',
            'table' => 'codalia_songbook_categories_songs',
            'order' => 'name'
        ]
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];


    public function getStatusOptions()
    {
      return array('unpublished' => 'codalia.songbook::lang.status.unpublished',
		   'published' => 'codalia.songbook::lang.status.published',
		   'trashed' => 'codalia.songbook::lang.status.trashed',
		   'archived' => 'codalia.songbook::lang.status.archived');
    }

    public function getUserRoleOptions() {
        $results = Db::table('backend_user_roles')->select('code', 'name')->where('code', '!=', '')->get();

        $options = array();

	foreach ($results as $option) {
	    $options[$option->code] = $option->name;
	}

	return $options;
    }

    public function getUpdatedByFieldAttribute() {
      $names = '';

      if($this->updated_by) {
	$user = BackendAuth::findUserById($this->updated_by);
	$names = $user->first_name.' '.$user->last_name;
      }

      return $names;
    }

    public function getStatusFieldAttribute() {
      $statuses = $this->getStatusOptions();
      $status = (isset($this->status)) ? $this->status : 'unpublished';

      return Lang::get($statuses[$status]);
    }

    public function beforeCreate()
    {
	if(empty($this->slug)) {
	  $this->slug = Str::slug($this->title);
	}

	$this->published_up = self::setPublishingDate($this);
	//$this->published_up = ($this->status == 'published' && is_null($this->published_up)) ? Carbon::now() : $this->published_up;
	$user = BackendAuth::getUser();
	$this->created_by = $user->id;
    }

    public function beforeUpdate()
    {
	//$this->published_up = ($this->status == 'published' && is_null($this->published_up)) ? Carbon::now() : $this->published_up;
	$this->published_up = self::setPublishingDate($this);
	$user = BackendAuth::getUser();
	$this->updated_by = $user->id;
    }

    /**
     * Sets the "url" attribute with a URL to this object.
     * @param string $pageName
     * @param Controller $controller
     *
     * @return string
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id'   => $this->id,
            'slug' => $this->slug
        ];

        $params['category'] = $this->categories->count() ? $this->categories->first()->slug : null;

        // Expose published year, month and day as URL parameters.
        if ($this->published) {
            $params['year']  = $this->published_up->format('Y');
            $params['month'] = $this->published_up->format('m');
            $params['day']   = $this->published_up->format('d');
        }

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    /**
     * Switch visibility of some fields according to the current user accesses.
     *
     * @param       $fields
     * @param  null $context
     * @return void
     */
    public function filterFields($fields, $context = null)
    {

        if ($context == 'update') {
	  if (strcmp($fields->created_at->value->toDateTimeString(), $fields->updated_at->value->toDateTimeString()) === 0) {
	      $fields->updated_at->hidden = true;
	      $fields->_updated_by_field->hidden = true;
	  }
	}

        if (!isset($fields->_status_field)) {
            return;
	}

        $user = BackendAuth::getUser();

        if($user->hasAccess('codalia.songbook.access_publish')) {
            $fields->_status_field->hidden = true;
        }

	if (isset($fields->_created_by_field) && $user->hasAccess('codalia.songbook.access_other_songs')) {
            $fields->_created_by_field->hidden = true;
        }
    }

    public static function setPublishingDate($song)
    {
	// Sets to the current date time in case the record has never been published before. 
	return ($song->status == 'published' && is_null($song->published_up)) ? Carbon::now() : $song->published_up;
    }

    /**
     * Used to test if a certain user has permission to edit song,
     * returns TRUE if the user is the owner or has other songs access.
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return ($this->created_by == $user->id) || $user->hasAnyAccess(['codalia.songbook.access_other_songs']);
    }

    public function canView()
    {
        //var_dump($this->access_id);
	if ($this->access_id === null) {
	    return true;
	}

	if (Auth::check()) {
	    $userGroups = Auth::getUser()->getGroups();

	    foreach ($userGroups as $userGroup) {
	      if ($userGroup->id == $this->access_id) {
		  return true;
	      }
	    }
	}

	return false;
    }

    /**
     * Returns the HTML content before the <!-- more --> tag or a limited 600
     * character version.
     *
     * @return string
     */
    public function getSummaryAttribute()
    {
        $more = '<!-- more -->';

        if (strpos($this->lyrics, $more) !== false) {
            $parts = explode($more, $this->lyrics);

            return array_get($parts, 0);
        }

        return Html::limit($this->lyrics, Settings::get('max_characters', 600));
    }

    //
    // Scopes
    //

    /**
     * Allows filtering for specific categories.
     * @param  Illuminate\Query\Builder  $query      QueryBuilder
     * @param  array                     $categories List of category ids
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterCategories($query, $categories)
    {
        return $query->whereHas('categories', function($q) use ($categories) {
            $q->whereIn('id', $categories);
        });
    }

    public function scopeIsPublished($query)
    {
        return $query->whereNotNull('status')
		     ->where('status', 'published')
		     ->whereNotNull('published_up')
		     ->where('published_up', '<', Carbon::now())
		     // Groups constraints within parenthesis.
		     ->where(function ($orWhere) {
			   $orWhere->whereNull('published_down')->orWhereColumn('published_down', '<', 'published_up');
		     });
    }

    /**
     * Apply a constraint to the query to find the nearest sibling
     *
     *     // Get the next song
     *     Song::applySibling()->first();
     *
     *     // Get the previous song
     *     Song::applySibling(-1)->first();
     *
     *     // Get the previous song, ordered by the ID attribute instead
     *     Song::applySibling(['direction' => -1, 'attribute' => 'id'])->first();
     *
     * @param       $query
     * @param array $options
     * @return
     */
    public function scopeApplySibling($query, $options = [])
    {
        if (!is_array($options)) {
            $options = ['direction' => $options];
        }

        extract(array_merge([
            'direction' => 'next',
            'attribute' => 'sort_order'
        ], $options));

        $isPrevious = in_array($direction, ['previous', -1]);
        $directionOrder = $isPrevious ? 'asc' : 'desc';
        $directionOperator = $isPrevious ? '>' : '<';

        $query->where('id', '<>', $this->id);

        if (!is_null($this->$attribute)) {
            $query->where($attribute, $directionOperator, $this->$attribute);
	}

        return $query->orderBy($attribute, $directionOrder);
    }

    /**
     * Returns the next song, if available.
     * @return self
     */
    public function nextSong()
    {
        return self::isPublished()->applySibling()->first();
    }

    /**
     * Returns the previous song, if available.
     * @return self
     */
    public function previousSong()
    {
        return self::isPublished()->applySibling(-1)->first();
    }

    /**
     * Lists songs for the frontend
     *
     * @param        $query
     * @param  array $options Display options
     * @return Song
     */
    public function scopeListFrontEnd($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'page'             => 1,
            'perPage'          => 30,
            'sort'             => 'created_at',
            'categories'       => null,
            'exceptCategories' => null,
            'category'         => null,
            'search'           => '',
            'published'        => true,
            'exceptSong'       => null
        ], $options));

        $searchableFields = ['title', 'slug', 'lyrics', 'content'];

        if ($published) {
            $query->isPublished();
        }

        /*
         * Except song(s)
         */
        if ($exceptSong) {
            $exceptSongs = (is_array($exceptSong)) ? $exceptSong : [$exceptSong];
            $exceptSongIds = [];
            $exceptSongSlugs = [];

            foreach ($exceptSongs as $exceptSong) {
                $exceptSong = trim($exceptSong);

                if (is_numeric($exceptSong)) {
                    $exceptSongIds[] = $exceptSong;
                } else {
                    $exceptSongSlugs[] = $exceptSong;
                }
            }

            if (count($exceptSongIds)) {
                $query->whereNotIn('id', $exceptSongIds);
            }
            if (count($exceptSongSlugs)) {
                $query->whereNotIn('slug', $exceptSongSlugs);
            }
        }

        /*
         * Sorting
         */
        if (in_array($sort, array_keys(static::$allowedSortingOptions))) {
            if ($sort == 'random') {
                $query->inRandomOrder();
            } else {
                @list($sortField, $sortDirection) = explode(' ', $sort);

                if (is_null($sortDirection)) {
                    $sortDirection = "desc";
                }

                $query->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Search
         */
        $search = trim($search);
        if (strlen($search)) {
            $query->searchWhere($search, $searchableFields);
        }

        /*
         * Categories
         */
        if ($categories !== null) {
            $categories = is_array($categories) ? $categories : [$categories];
            $query->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }

        /*
         * Except Categories
         */
        if (!empty($exceptCategories)) {
            $exceptCategories = is_array($exceptCategories) ? $exceptCategories : [$exceptCategories];
            array_walk($exceptCategories, 'trim');

            $query->whereDoesntHave('categories', function ($q) use ($exceptCategories) {
                $q->whereIn('slug', $exceptCategories);
            });
        }

        /*
         * Category, including children
         */
        if ($category !== null) {
            $category = Category::find($category);

            $categories = $category->getAllChildrenAndSelf()->lists('id');
            $query->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }
//dd($query->toSql());
        return $query->paginate($perPage, $page);
    }
}
