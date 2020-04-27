<?php

return [
    'plugin' => [
        'name' => 'Song Book',
        'description' => ''
    ],
    'songbook' => [
      'songs' => 'Songs',
      'categories' => 'Categories',
      'tab' => 'Song Book',
      'access_songs' => 'Manage the songs',
      'access_categories' => 'Manage the song categories',
      'access_publish' => 'Allowed to publish songs',
      'access_delete' => 'Allowed to delete songs',
      'access_other_songs' => 'Manage other users songs',
      'manage_settings' => 'Manage Song Book settings',
    ],
    'songs' => [
      'new_song' => 'New song',
      'filter_category' => 'Category',
      'filter_date' => 'Date',
      'filter_status' => 'Status',
      'reorder' => 'Reorder Songs',
      'return_to_songs' => 'Return to the song list',
    ],
    'song' => [
      'title_placeholder' => 'New song title',
      'slug_placeholder' => 'new-song-slug',
      'tab_categories' => 'Categories',
      'categories_comment' => 'Select extra categories for the song',
      'categories_placeholder' => 'There are no categories, you should create one first!',
    ],
    'categories' => [
      'reorder' => 'Reorder Categories',
      'return_to_categories' => 'Return to the song category list',
    ],
    'category' => [
      'name_placeholder' => 'New category name',
      'slug_placeholder' => 'new-category-slug'
    ],
    // Boilerplate attributes.
    'attribute' => [
      'title' => 'Title',
      'name' => 'Name',
      'slug' => 'Slug',
      'description' => 'Description',
      'created_at' => 'Created at',
      'created_by' => 'Created by',
      'updated_at' => 'Updated at',
      'updated_by' => 'Updated by',
      'tab_edit' => 'Edit',
      'tab_manage' => 'Manage',
      'status' => 'Status',
      'published_up' => 'Start publishing',
      'published_down' => 'Finish publishing',
      'access' => 'Access',
      'viewing_access' => 'Viewing access',
      'main_category' => 'Main category',
    ],
    'status' => [
      'published' => 'Published',
      'unpublished' => 'Unpublished',
      'trashed' => 'Trashed',
      'archived' => 'Archived'
    ],
    'action' => [
      'new' => 'New Song',
      'publish' => 'Publish',
      'unpublish' => 'Unpublish',
      'trash' => 'Trash',
      'archive' => 'Archive',
      'delete' => 'Delete',
      'publish_success' => 'Successfully published those items.',
      'unpublish_success' => 'Successfully unpublished those items.',
      'archive_success' => 'Successfully archived those items.',
      'trash_success' => 'Successfully trashed those items.',
      'delete_success' => 'Successfully deleted those items.',
      'parent_item_unpublished' => 'Cannot publish this item as its parent item is unpublished.',
      'previous' => 'Previous',
      'next' => 'Next',
    ],
    'sorting' => [
        'title_asc' => 'Title (ascending)',
        'title_desc' => 'Title (descending)',
        'created_asc' => 'Created (ascending)',
        'created_desc' => 'Created (descending)',
        'updated_asc' => 'Updated (ascending)',
        'updated_desc' => 'Updated (descending)',
        'published_asc' => 'Published (ascending)',
        'published_desc' => 'Published (descending)',
        'random' => 'Random'
    ],
    'settings' => [
      'category_title' => 'Category List',
      'category_description' => 'Displays a list of song categories on the page.',
      'category_slug' => 'Category slug',
      'category_slug_description' => "Look up the song category using the supplied slug value. This property is used by the default component partial for marking the currently active category.",
      'category_display_empty' => 'Display empty categories',
      'category_display_empty_description' => 'Show categories that do not have any songs.',
      'category_page' => 'Category page',
      'category_page_description' => 'Name of the category page file for the category links. This property is used by the default component partial.',
      'group_links' => 'Links',
      'song_title' => 'Song',
      'song_description' => 'Displays a song on the page.',
      'song_slug' => 'Song slug',
      'song_slug_description' => "Look up the song using the supplied slug value.",
      'song_category' => 'Category page',
      'song_category_description' => 'Name of the category page file for the category links. This property is used by the default component partial.',
      'songs_title' => 'Song List',
      'songs_description' => 'Displays a list of latest songs on the page.',
      'songs_pagination' => 'Page number',
      'songs_pagination_description' => 'This value is used to determine what page the user is on.',
      'songs_filter' => 'Category filter',
      'songs_filter_description' => 'Enter a category slug or URL parameter to filter the songs by. Leave empty to show all songs.',
      'songs_per_page' => 'Songs per page',
      'songs_per_page_validation' => 'Invalid format of the songs per page value',
      'songs_no_songs' => 'No songs message',
      'songs_no_songs_description' => 'Message to display in the song list in case if there are no songs. This property is used by the default component partial.',
      'songs_no_songs_default' => 'No songs found',
      'songs_order' => 'Song order',
      'songs_order_description' => 'Attribute on which the songs should be ordered',
      'songs_category' => 'Category page',
      'songs_category_description' => 'Name of the category page file for the "Posted into" category links. This property is used by the default component partial.',
      'songs_song' => 'Song page',
      'songs_song_description' => 'Name of the song page file for the "Learn more" links. This property is used by the default component partial.',
      'songs_except_song' => 'Except song',
      'songs_except_song_description' => 'Enter ID/URL or variable with song ID/URL you want to exclude. You may use a comma-separated list to specify multiple songs.',
      'songs_except_song_validation' => 'Song exceptions must be a single slug or ID, or a comma-separated list of slugs and IDs',
      'songs_except_categories' => 'Except categories',
      'songs_except_categories_description' => 'Enter a comma-separated list of category slugs or variable with such a list of categories you want to exclude',
      'songs_except_categories_validation' => 'Category exceptions must be a single category slug, or a comma-separated list of slugs',
      'group_exceptions' => 'Exceptions'
    ],
    'global_settings' => [
      'tab_general' => 'General',
      'max_characters' => 'Max characters',
      'max_characters_comment' => 'Max characters',
    ],
    'messages' => [
      'required_field' => 'This field is required'
    ]
];
