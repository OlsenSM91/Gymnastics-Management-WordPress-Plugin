<?php
function gm_coaches_page() {
    global $wpdb;

    // Handle adding a new coach
    if (isset($_POST['action']) && $_POST['action'] === 'add_coach') {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);

        wp_insert_post(array(
            'post_title' => $first_name . ' ' . $last_name,
            'post_type' => 'gm_coach',
            'post_status' => 'publish',
            'meta_input' => array(
                '_gm_coach_first_name' => $first_name,
                '_gm_coach_last_name' => $last_name,
                '_gm_coach_phone' => $phone,
                '_gm_coach_email' => $email,
            ),
        ));
    }

    // Handle deleting a coach
    if (isset($_GET['delete_coach'])) {
        $coach_id = intval($_GET['delete_coach']);
        wp_delete_post($coach_id);
        echo '<meta http-equiv="refresh" content="0; url=?page=gym-coaches">';
    }

    // Fetch all coaches
    $coaches = get_posts(array(
        'post_type' => 'gm_coach',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));

    echo '<h1>Coaches Management</h1>';

    // Add Coach Form
    echo '<h2>Add New Coach</h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="action" value="add_coach">';
    echo '<label for="first_name">First Name</label>';
    echo '<input type="text" name="first_name" id="first_name" required>';
    echo '<br><label for="last_name">Last Name</label>';
    echo '<input type="text" name="last_name" id="last_name" required>';
    echo '<br><label for="phone">Phone Number</label>';
    echo '<input type="text" name="phone" id="phone" required>';
    echo '<br><label for="email">Email</label>';
    echo '<input type="email" name="email" id="email" required>';
    echo '<br><input type="submit" value="Add Coach">';
    echo '</form>';

    // Existing Coaches
    echo '<h2>Existing Coaches</h2>';
    echo '<ul>';
    foreach ($coaches as $coach) {
        $first_name = get_post_meta($coach->ID, '_gm_coach_first_name', true);
        $last_name = get_post_meta($coach->ID, '_gm_coach_last_name', true);
        $phone = get_post_meta($coach->ID, '_gm_coach_phone', true);
        $email = get_post_meta($coach->ID, '_gm_coach_email', true);

        echo '<li>';
        echo esc_html($first_name) . ' ' . esc_html($last_name) . ' - ' . esc_html($phone) . ' - ' . esc_html($email);
        echo ' <a href="?page=gym-coaches&delete_coach=' . $coach->ID . '">Delete</a>';
        echo '</li>';
    }
    echo '</ul>';
}
?>