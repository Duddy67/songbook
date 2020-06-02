<?php namespace Codalia\SongBook\Models;

use October\Rain\Database\Model;

class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'codalia_songbook_settings';

    public $settingsFields = 'fields.yaml';

    public $rules = [
        'show_all_songs' => ['boolean'],
        'show_breadcrumb' => ['boolean'],
    ];
}
