<?php namespace Codalia\SongBook\Controllers;

use Flash;
use Lang;
use Carbon\Carbon;
use BackendMenu;
use Backend\Classes\Controller;
use Codalia\SongBook\Models\Song;
use Backend\Behaviors\FormController;


/**
 * Songs Back-end Controller
 */
class Songs extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['codalia.songbook.access_songs'];


    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Codalia.SongBook', 'songbook', 'songs');
    }


    public function index()
    {
	$this->vars['statusIcons'] = self::getStatusIcons();

	// Calls the parent method as an extension.
        $this->asExtension('ListController')->index();
    }

    public function create()
    {
	BackendMenu::setContextSideMenu('new_song');

	return $this->asExtension('FormController')->create();
    }

    public function update($recordId = null, $context = null)
    {
        return $this->asExtension('FormController')->update($recordId, $context);
    }

    public function listExtendQuery($query)
    {
	if (!$this->user->hasAnyAccess(['codalia.songbook.access_other_songs'])) {
	    $query->where('created_by', $this->user->id);
	}
    }

    public function formExtendQuery($query)
    {
        if (!$this->user->hasAnyAccess(['codalia.songbook.access_other_songs'])) {
	    $query->where('created_by', $this->user->id);
	}
    }

    public function index_onDelete()
    {
	// Needed for the status column partial.
	$this->vars['statusIcons'] = self::getStatusIcons();

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
	$this->vars['statusIcons'] = self::getStatusIcons();

	// Ensures one or more items are selected.
	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
	  $status = post('status');
	  foreach ($checkedIds as $songId) {
	      $song = Song::find($songId);
	      $song->status = $status;
	      $song->published_up = Song::setPublishingDate($song);
	      $song->save();
	  }

	  $toRemove = ($status == 'archived') ? 'd' : 'ed';

	  Flash::success(Lang::get('codalia.songbook::lang.action.'.rtrim($status, $toRemove).'_success'));
	}

	return $this->listRefresh();
    }

    public function update_onSave($recordId = null, $context = null)
    {
	// Calls the original update_onSave method
	return $this->asExtension('FormController')->update_onSave($recordId, $context);
    }

    public function listInjectRowClass($record, $definition = null)
    {
        if ($record->status == 'archived') {
            return 'safe disabled';
        }
    }

    public static function getStatusIcons()
    {
	// Returns the css status mapping.
        return ['published' => 'success', 'unpublished' => 'danger', 'archived' => 'muted', 'trashed' => 'danger']; 
    }
}
