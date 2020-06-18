<?php namespace Codalia\SongBook\Helpers;

use October\Rain\Support\Traits\Singleton;
use Carbon\Carbon;
use Db;


class SongBookHelper
{
    use Singleton;


    /**
     * Checks out a given item for a given user.
     *
     * @param string  $tableName
     * @param User    $user
     * @param integer $recordId
     *
     * @return void
     */
    public function checkOut($tableName, $user, $recordId)
    {
	Db::table($tableName)->where('id', $recordId)
			     ->update(['checked_out' => $user->id, 
				       'checked_out_time' => Carbon::now()]);
    }

    /**
     * Checks in an item table. The "check in" can be more specific according to the
     * optional parameters passed.
     *
     * @param string  $tableName
     * @param User    $user (optional)
     * @param integer $recordId (optional)
     *
     * @return void
     */
    public function checkIn($tableName, $user = null, $recordId = null)
    {
	Db::table($tableName)->where(function($query) use($user, $recordId) {
	                                 if ($user) {
					     $query->where('checked_out', $user->id);
					 }

	                                 if ($recordId) {
					     $query->where('id', $recordId);
					 }
				    })->update(['checked_out' => 0,
						'checked_out_time' => null]);
    }

    public function getCheckInHTML($record, $user)
    {
	$name = $user->first_name.' '.$user->last_name;
	$link = '<a href="#" title="'.$name.'">';
	$html = '<div class="checked-out">'.$link.$record->title.'</a><span class="lock"></span></div>';

	return $html;
    }

    public function getStatusIcons()
    {
	// Returns the css status mapping.
        return ['published' => 'success', 'unpublished' => 'danger', 'archived' => 'muted']; 
    }
}
