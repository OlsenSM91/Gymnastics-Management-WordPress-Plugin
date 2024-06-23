<?php
function gm_levels_page() {
    global $wpdb;

    // Handle adding a new level
    if (isset($_POST['action']) && $_POST['action'] === 'add_level') {
        $level_name = sanitize_text_field($_POST['level_name']);

        wp_insert_term($level_name, 'gm_level');
    }

    // Handle deleting a level
    if (isset($_GET['delete_level'])) {
        $level_id = intval($_GET['delete_level']);
        wp_delete_term($level_id, 'gm_level');
        echo '<meta http-equiv="refresh" content="0; url=?page=gym-levels">';
    }

    // Fetch all levels
    $levels = get_terms(array(
        'taxonomy' => 'gm_level',
        'hide_empty' => false,
    ));

    echo '<h1>Levels Management</h1>';

    // Add Level Form
    echo '<h2>Add New Level</h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="action" value="add_level">';
    echo '<label for="level_name">Level Name</label>';
    echo '<input type="text" name="level_name" id="level_name" required>';
    echo '<br><input type="submit" value="Add Level">';
    echo '</form>';

    // Existing Levels
    echo '<h2>Existing Levels</h2>';
    echo '<ul>';
    foreach ($levels as $level) {
        echo '<li>' . esc_html($level->name);
        echo ' <a href="?page=gym-levels&delete_level=' . $level->term_id . '">Delete</a>';
        echo '</li>';
    }
    echo '</ul>';
}
?>