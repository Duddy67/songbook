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
    public $hasMany = [];
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
      if ($this->status == 'unpublished') {
	// All of the children items have to be unpublished as well.
	foreach ($this->getAllChildren() as $children) {
	  $children->status = 'unpublished';
	  $children->save();
	}
      }
    }

    /**
     * Count songs in this and nested categories
     * @return int
     */
    public function getNestedSongCount()
    {
      //echo 'TEST'.$this->name.' '.$this->songs_count()->count();
        return $this->songs_count()->count() + $this->children->sum(function ($category) {
            return $category->getNestedSongCount();
        });
    }


    public function getStatusOptions()
    {
      return array('unpublished' => 'codalia.songbook::lang.status.unpublished',
		   'published' => 'codalia.songbook::lang.status.published');
    }

    /**
     * Sets the "url" attribute with a URL to this object
     *
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     *
     * @return string
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id'   => $this->id,
            'slug' => $this->slug
        ];
      //var_dump($controller->pageUrl($pageName, $params, false));

        return $this->url = $controller->pageUrl($pageName, $params, false);
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
