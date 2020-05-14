<?php 

// Redirects all the orderings views except for the reorder one.

Route::get('backend/codalia/songbook/orderings', function() {
    return redirect('backend/codalia/songbook/songs');
});

Route::get('backend/codalia/songbook/orderings/create', function() {
    return redirect('backend/codalia/songbook/songs');
});

Route::get('backend/codalia/songbook/orderings/update/{id}', function() {
    return redirect('backend/codalia/songbook/songs');
});

Route::get('backend/codalia/songbook/orderings/preview/{id}', function() {
    return redirect('backend/codalia/songbook/songs');
});
