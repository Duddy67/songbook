<?php namespace Codalia\SongBook\Controllers;

use Flash;
use Lang;
use BackendMenu;
use Backend\Classes\Controller;
use Codalia\SongBook\Models\Category;

/**
 * Categories Back-end Controller
 */
class Categories extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    // css status mapping.
    public $statusIcons = ['published' => 'success', 'unpublished' => 'info'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Codalia.SongBook', 'songbook', 'categories');
    }

    public function index()
    {
	$this->vars['statusIcons'] = $this->statusIcons;

	// Calls the parent method as an extension.
        $this->asExtension('ListController')->index();
    }

    public function index_onSetStatus()
    {
	// Needed for the status column partial.
	$this->vars['statusIcons'] = $this->statusIcons;

	// Ensures one or more items are selected.
	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
	    $status = post('status');

	    foreach ($checkedIds as $catId) {
	      $category = Category::find($catId);

	      if ($status == 'unpublished') {
		  // All of the children items have to be unpublished as well.
		  foreach ($category->getAllChildren() as $children) {
		      $children->status = $status;
		      $children->save();
		  }
	      }
	      // published
	      else {
		  // Gets the parent item if any.
		  $parent = Category::find($category->getParentId());
		  // Do not publish the item if the parent item is unpublished.
		  if ($parent && $parent->getAttributeValue('status') == 'unpublished') {
		      Flash::warning(Lang::get('codalia.songbook::lang.action.parent_item_unpublished'));
		      return false;
		  }
	      }

	      // Assigns the new status value to the selected item.
	      $category->status = $status;
	      $category->save();
	  }

	  Flash::success(Lang::get('codalia.songbook::lang.action.'.rtrim($status, 'ed').'_success'));
      }

      return $this->listRefresh();
    }

    public function onReorder()
    {
	parent::onReorder();

	// Refreshes the category path.
	$category = Category::find(post('sourceNode'));
	Category::setCategoryPath($category);
    }
}
