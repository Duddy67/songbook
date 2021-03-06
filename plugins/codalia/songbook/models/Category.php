<?php namespace Codalia\SongBook\Models;

use Model;
use Lang;

/**
 * Category Model
 */
class Category extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\NestedTree;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'codalia_songbook_categories';

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
    public $rules = [];

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
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'orderings' => [
            'Codalia\SongBook\Models\Ordering',
        ]
    ];
    public $belongsTo = [];
    public $belongsToMany = [
      'songs' => ['Codalia\SongBook\Models\Song',
	  'table' => 'codalia_songbook_categories_songs',
	  'order' => 'published_at desc',
	  //'scope' => 'isPublished'
      ],
      'songs_count' => ['Codalia\SongBook\Models\Song',
	  'table' => 'codalia_songbook_categories_songs',
	  'count' => true,
	  'scope' => 'songCount',
      ],
 
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];


    public function beforeCreate()
    {
        // Ensure that the left and right columns are set from the start.
	$this->setDefaultLeftAndRight();
    }

    public function beforeSave()
    {
	// Gets the parent category if any.
	$parent = Category::find($this->getParentId());
	// Do not publish this category if its parent is unpublished.
	if ($parent && $parent->getAttributeValue('status') == 'unpublished' && $this->status == 'published') {
	    throw new \ApplicationException(Lang::get('codalia.songbook::lang.action.parent_item_unpublished'));
	}
    }

    public function afterSave()
    {
	self::setCategoryPath($this);

	foreach ($this->getAllChildren() as $children) {
	    self::setCategoryPath($children);

	    if ($this->status == 'unpublished') {
		// All of the children items have to be unpublished as well.
		\Db::table('codalia_songbook_categories')->where('id', $children->id)->update(['status' => 'unpublished']);
	    }
	}
    }

    /**
     * Count songs in this and nested categories
     * @return int
     */
    public function getNestedSongCount()
    {
        return $this->songs_count()->count() + $this->children->sum(function ($category) {
            return $category->getNestedSongCount();
        });
    }


    public function getStatusOptions()
    {
	return array('unpublished' => 'codalia.songbook::lang.status.unpublished',
		     'published' => 'codalia.songbook::lang.status.published');
    }

    public function getStatusFieldAttribute()
    {
	$statuses = $this->getStatusOptions();
	$status = (isset($this->status)) ? $this->status : 'unpublished';

	return Lang::get($statuses[$status]);
    }

    public function getParentFieldAttribute()
    {
        if ($this->parent) {
	    return $this->parent->attributes['name'];
	}

	return Lang::get('codalia.songbook::lang.attribute.none');
    }

    /**
     * Switch visibility of some fields according to the parent and status values.
     *
     * @param       $fields
     * @param  null $context
     * @return void
     */
    public function filterFields($fields, $context = null)
    {
        if ($this->parent && $this->parent->attributes['status'] == 'unpublished') {
	    $fields->status->cssClass = 'hidden';
            $fields->parent->cssClass = 'hidden';
            $fields->_status_field->cssClass = 'visible';
            $fields->_parent_field->cssClass = 'visible';
	}
	elseif ($this->parent && $this->parent->attributes['status'] == 'published' && $this->status == 'unpublished') {
            $fields->parent->cssClass = 'hidden';
            $fields->_parent_field->cssClass = 'visible';
            $fields->_status_field->cssClass = 'hidden';
	}
	else {
            $fields->_parent_field->cssClass = 'hidden';
            $fields->_status_field->cssClass = 'hidden';
	}
    }

    /**
     * Sets the "url" attribute with a URL to this object
     *
     * @param Cms\Classes\Controller $controller
     *
     * @return string
     */
    public function setUrl($controller)
    {
        $params = [
            'id'   => $this->id,
	    'slug' => $this->slug,
        ];

	$this->path = self::getCategoryPath($this);
	$level = count($this->path);
	// Sets the category page with the appropriate url pattern.
	$pageName = 'category-level-'.$level;

	// The given category has parents.
	if ($level > 1) {
	    // Loops through the category path.
	    foreach ($this->path as $key => $category) {
	        $i = $key + 1;

		// Don't treat the last element as it's the given category itself.
		if ($i == $level) {
		    break;
		}

		// Sets the parents of the given category.
	        $params['parent-'.$i] = $category['slug']; 
	    }
	}

        return $this->url = $controller->pageUrl($pageName, $params, false);
    }

    /**
     * Returns the category path to a given category.
     *
     * @param object $category
     *
     * @return array
     */
    public static function getCategoryPath($category)
    {
        $path = [$category->attributes];
	$parent = $category->getParent()->first();

	// Goes up to the root parent.
	while ($parent) {
	    $path[] = $parent->attributes;
	    $parent = $parent->getParent()->first();
	}

        return array_reverse($path);
    }

    /**
     * Builds and sets the path attribute for a given category.
     *
     * @param object $category
     *
     * @return void
     */
    public static function setCategoryPath($category)
    {
	$categoryPath = self::getCategoryPath($category);
	$path = '';

	// Builds the path.
	foreach ($categoryPath as $segment) {
	    $path .= $segment['slug'].'/';
	}

	$path = substr($path, 0, -1);

	// Sets the path.
	\Db::table('codalia_songbook_categories')->where('id', $category->id)->update(['path' => $path]);
    }

    protected static function listSubCategoryOptions()
    {
        $category = self::getNested();

        $iterator = function($categories) use (&$iterator) {
            $result = [];

            foreach ($categories as $category) {
                if (!$category->children) {
                    $result[$category->id] = $category->name;
                }
                else {
                    $result[$category->id] = [
                        'title' => $category->name,
                        'items' => $iterator($category->children)
                    ];
                }
            }

            return $result;
        };

        return $iterator($category);
    }
}
