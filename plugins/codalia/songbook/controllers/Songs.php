<?php namespace Codalia\SongBook\Controllers;

use Flash;
use Lang;
use BackendMenu;
use Backend\Classes\Controller;
use Codalia\SongBook\Models\Song;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ReorderController;


/**
 * Songs Back-end Controller
 */
class Songs extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = ['codalia.songbook.access_songs'];

    // css status mapping.
    public $statusIcons = ['published' => 'success', 'unpublished' => 'info', 'archived' => 'muted', 'trashed' => 'danger'];


    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Codalia.SongBook', 'songbook', 'songs');
    }


    public function index()
    {
	$this->vars['statusIcons'] = $this->statusIcons;

	// Calls the parent method as an extension.
        $this->asExtension('ListController')->index();
    }

    public function create()
    {
	BackendMenu::setContextSideMenu('new_song');

	return $this->asExtension('FormController')->create();
    }

    public function update($recordId = null)
    {
        return $this->asExtension('FormController')->update($recordId);
    }

    public function listExtendQuery($query)
    {
	if (!$this->user->hasAnyAccess(['codalia.songbook.access_other_songs'])) {
	    $query->where('created_by', $this->user->id);
	}
	
	//dd($query->toSql());
    }

    public function formExtendQuery($query)
    {
        if (!$this->user->hasAnyAccess(['codalia.songbook.access_other_songs'])) {
	    $query->where('created_by', $this->user->id);
	}
    }

    public function index_onDelete()
    {
	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $songId) {
	        // Checks that song does exist and the current user has the required access levels.
                if ((!$song = Song::find($songId)) || !$song->canEdit($this->user)) {
                    continue;
                }

                $song->delete();
            }

            Flash::success(Lang::get('codalia.songbook::lang.action.delete_success'));
         }

        return $this->listRefresh();
    }

    public function index_onSetStatus()
    {
      // Needed for the status column partial.
      $this->vars['statusIcons'] = $this->statusIcons;

      // Ensures one or more items are selected.
      if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
	$status = post('status');
	foreach ($checkedIds as $songId) {
	  $song = Song::find($songId);
	  $song->status = $status;
	  $song->save();
	}

	$toRemove = ($status == 'archived') ? 'd' : 'ed';

	Flash::success(Lang::get('codalia.songbook::lang.action.'.rtrim($status, $toRemove).'_success'));
      }

      return $this->listRefresh();
    }

    public function update_onSave($recordId = null, $context = null)
    {
      //$fieldMarkup = $this->formGetWidget()->renderField('updated_at', ['useContainer' => true]);

      /*return [
	'#field-id' => $fieldMarkup
      ];*/

      //$this->formRenderField('update_at', ['useContainer'=>false]);
      // Calls the original update_onSave method
      return $this->asExtension('FormController')->update_onSave($recordId, $context);
    }

    public function reorder()
    {
	$this->vars['statusIcons'] = $this->statusIcons;
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
	    $query->filterCategories(array_keys($category));
	}
    }

    public function listInjectRowClass($record, $definition = null)
    {
        if ($record->status == 'archived') {
            return 'safe disabled';
        }
    }
}
