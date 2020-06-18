<?php namespace Codalia\SongBook\Controllers;

use Flash;
use Lang;
use Carbon\Carbon;
use BackendMenu;
use Backend\Classes\Controller;
use Codalia\SongBook\Models\Song;
use Backend\Behaviors\FormController;
use BackendAuth;
use Codalia\SongBook\Helpers\SongBookHelper;


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
	$this->addCss(url('plugins/codalia/songbook/assets/css/extra.css'));
	// Unlocks the checked out items of this user (if any).  
	SongBookHelper::instance()->checkIn((new Song)->getTable(), BackendAuth::getUser());
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
	$song = Song::find($recordId);
	$user = BackendAuth::getUser();

	// Checks for permissions.
	if (!$song->canEdit($user)) {
	    Flash::error(Lang::get('codalia.songbook::lang.action.editing_not_allowed'));
	    return redirect('backend/codalia/songbook/songs');
	}

	// Checks for check out matching.
	if ($song->checked_out && $user->id != $song->checked_out) {
	    Flash::error(Lang::get('codalia.songbook::lang.action.check_out_do_not_match'));
	    return redirect('backend/codalia/songbook/songs');
	}

        if ($context == 'edit') {
	    // Locks the item for this user.
	    SongBookHelper::instance()->checkOut((new Song)->getTable(), $user, $recordId);
	}

        return $this->asExtension('FormController')->update($recordId, $context);
    }

    public function listOverrideColumnValue($record, $columnName, $definition = null)
    {
        if ($record->checked_out && $columnName == 'title') {
	    return SongBookHelper::instance()->getCheckInHTML($record, BackendAuth::findUserById($record->checked_out));
	}
    }

    public function listExtendQuery($query)
    {
	if (!$this->user->hasAnyAccess(['codalia.songbook.access_other_songs'])) {
	    // Shows only the user's songs if they don't have access to other songs.
	    $query->where('created_by', $this->user->id);
	}
    }

    public function listInjectRowClass($record, $definition = null)
    {
        $class = '';

        if ($record->status == 'archived') {
            $class = 'safe disabled';
        }

        if ($record->checked_out) {
	    $class = 'safe disabled nolink';
	}

	return $class;
    }

    public function index_onDelete()
    {
	// Needed for the status column partial.
	$this->vars['statusIcons'] = self::getStatusIcons();

	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $recordId) {
	        // Checks that song does exist and the current user has the required access levels.
                if ((!$song = Song::find($recordId)) || !$song->canEdit($this->user) || $song->checked_out) {
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
	  foreach ($checkedIds as $recordId) {
	      $song = Song::find($recordId);

	      if ($song->checked_out) {
		  Flash::error(Lang::get('codalia.songbook::lang.action.checked_out_item'));
		  return $this->listRefresh();
	      }

	      $song->status = $status;
	      $song->published_up = Song::setPublishingDate($song);
	      $song->save();
	  }

	  $toRemove = ($status == 'archived') ? 'd' : 'ed';

	  Flash::success(Lang::get('codalia.songbook::lang.action.'.rtrim($status, $toRemove).'_success'));
	}

	return $this->listRefresh();
    }

    public function index_onCheckIn()
    {
	// Needed for the status column partial.
	$this->vars['statusIcons'] = self::getStatusIcons();

	// Ensures one or more items are selected.
	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
	  foreach ($checkedIds as $recordId) {
	      SongBookHelper::instance()->checkIn((new Song)->getTable(), null, $recordId);
	  }

	  Flash::success(Lang::get('codalia.songbook::lang.action.check_in_success'));
	}

	return $this->listRefresh();
    }

    public function update_onSave($recordId = null, $context = null)
    {
	// Calls the original update_onSave method
	return $this->asExtension('FormController')->update_onSave($recordId, $context);
    }

    public static function getStatusIcons()
    {
	// Returns the css status mapping.
        return ['published' => 'success', 'unpublished' => 'danger', 'archived' => 'muted']; 
    }
}
