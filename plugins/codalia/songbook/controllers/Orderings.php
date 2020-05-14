<?php namespace Codalia\SongBook\Controllers;

use BackendMenu;
use Flash;
use Lang;
use Backend\Classes\Controller;
use Backend\Behaviors\ReorderController;
use Codalia\SongBook\Controllers\Songs;

/**
 * Orderings Back-end Controller
 */
class Orderings extends Controller
{
    public $implement = [
        'Backend.Behaviors.ReorderController',
    ];

    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Codalia.SongBook', 'songbook', 'orderings');
    }

    public function reorder()
    {
	$this->vars['statusIcons'] = Songs::getStatusIcons();
	$this->addCss(url('plugins/codalia/songbook/assets/css/extra.css'));

        $this->asExtension('ReorderController')->reorder();
    }

    public function getCurrentFilters($name = null) {
        foreach (\Session::get('widget', []) as $key => $item) {
            if (str_contains($key, 'Filter')) {
                $filters = @unserialize(@base64_decode($item));
                if ($filters) {
		    if (array_key_exists('scope-'.$name, $filters)) {
		        $filter = (isset($filters['scope-'.$name])) ? $filters['scope-'.$name] : [];
		        return $filter;
		    }

		    return $filters;
                }
		else {
		    return [];
		}
            }
        }

	return [];
    }

    public function reorderExtendQuery($query)
    {
        $category = $this->getCurrentFilters('category');

        if (count($category) == 1) {
	    $query->where('category_id', array_keys($category)); 
	}
	else {
	    // Cancels the query. No item is returned.
	    $query->whereRaw('1 = 0');
	    Flash::warning(Lang::get('codalia.songbook::lang.action.cannot_reorder'));
	}
    }
}
