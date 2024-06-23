<?php
function gm_parents_page() {
    global $wpdb;

    // Handle adding a new parent
    if (isset($_POST['action']) && $_POST['action'] === 'add_parent') {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $address = sanitize_text_field($_POST['address']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);

        $parent_id = wp_insert_post(array(
            'post_title' => $first_name . ' ' . $last_name,
            'post_type' => 'gm_parent',
            'post_status' => 'publish',
            'meta_input' => array(
                '_gm_parent_first_name' => $first_name,
                '_gm_parent_last_name' => $last_name,
                '_gm_parent_address' => $address,
                '_gm_parent_phone' => $phone,
                '_gm_parent_email' => $email,
            ),
        ));
    }

    // Handle adding a new athlete
    if (isset($_POST['action']) && $_POST['action'] === 'add_athlete') {
        $athlete_first_name = sanitize_text_field($_POST['athlete_first_name']);
        $athlete_last_name = sanitize_text_field($_POST['athlete_last_name']);
        $athlete_gender = sanitize_text_field($_POST['athlete_gender']);
        $athlete_dob = sanitize_text_field($_POST['athlete_dob']);
        $athlete_allergies = sanitize_text_field($_POST['athlete_allergies']);
        $athlete_medical_info = sanitize_text_field($_POST['athlete_medical_info']);
        $parent_id = intval($_POST['parent_id']);

        $athletes = get_post_meta($parent_id, '_gm_parent_athletes', true);
        if (!$athletes) {
            $athletes = array();
        }
        $athlete_id = uniqid('athlete_', true);  // Generate a unique ID with a prefix
        $athletes[$athlete_id] = array(
            'id' => $athlete_id,
            'first_name' => $athlete_first_name,
            'last_name' => $athlete_last_name,
            'gender' => $athlete_gender,
            'dob' => $athlete_dob,
            'allergies' => $athlete_allergies,
            'medical_info' => $athlete_medical_info,
        );
        update_post_meta($parent_id, '_gm_parent_athletes', $athletes);
    }

    // Handle deleting a parent
    if (isset($_GET['delete_parent'])) {
        $parent_id = intval($_GET['delete_parent']);
        wp_delete_post($parent_id);
        echo '<meta http-equiv="refresh" content="0; url=?page=gym-parents">';
    }

    // Fetch all parents
    $parents = get_posts(array(
        'post_type' => 'gm_parent',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));

    echo '<h1>Parents Management</h1>';

    // Add Parent Form
    echo '<h2>Add New Parent</h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="action" value="add_parent">';
    echo '<label for="first_name">First Name</label>';
    echo '<input type="text" name="first_name" id="first_name" required>';
    echo '<br><label for="last_name">Last Name</label>';
    echo '<input type="text" name="last_name" id="last_name" required>';
    echo '<br><label for="address">Address</label>';
    echo '<input type="text" name="address" id="address" required>';
    echo '<br><label for="phone">Phone Number</label>';
    echo '<input type="text" name="phone" id="phone" required>';
    echo '<br><label for="email">Email</label>';
    echo '<input type="email" name="email" id="email" required>';
    echo '<br><input type="submit" value="Add Parent">';
    echo '</form>';

    // Add Athlete Form
    echo '<h2>Add New Athlete to Parent</h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="action" value="add_athlete">';
    echo '<label for="parent_id">Select Parent</label>';
    echo '<select name="parent_id" id="parent_id" required>';
    foreach ($parents as $parent) {
        echo '<option value="' . $parent->ID . '">' . esc_html($parent->post_title) . '</option>';
    }
    echo '</select>';
    echo '<br><label for="athlete_first_name">Athlete\'s First Name</label>';
    echo '<input type="text" name="athlete_first_name" id="athlete_first_name" required>';
    echo '<br><label for="athlete_last_name">Athlete\'s Last Name</label>';
    echo '<input type="text" name="athlete_last_name" id="athlete_last_name" required>';
    echo '<br><label for="athlete_gender">Gender</label>';
    echo '<select name="athlete_gender" id="athlete_gender" required>';
    echo '<option value="male">Male</option>';
    echo '<option value="female">Female</option>';
    echo '</select>';
    echo '<br><label for="athlete_dob">Date of Birth</label>';
    echo '<input type="date" name="athlete_dob" id="athlete_dob" required>';
    echo '<br><label for="athlete_allergies">Allergies</label>';
    echo '<input type="text" name="athlete_allergies" id="athlete_allergies">';
    echo '<br><label for="athlete_medical_info">Medical Info</label>';
    echo '<input type="text" name="athlete_medical_info" id="athlete_medical_info">';
    echo '<br><input type="submit" value="Add Athlete">';
    echo '</form>';

    // Existing Parents and Athletes
    echo '<h2>Existing Parents and Athletes</h2>';
    echo '<ul>';
    foreach ($parents as $parent) {
        $first_name = get_post_meta($parent->ID, '_gm_parent_first_name', true);
        $last_name = get_post_meta($parent->ID, '_gm_parent_last_name', true);
        $address = get_post_meta($parent->ID, '_gm_parent_address', true);
        $phone = get_post_meta($parent->ID, '_gm_parent_phone', true);
        $email = get_post_meta($parent->ID, '_gm_parent_email', true);

        echo '<li>';
        echo '<strong>' . esc_html($first_name) . ' ' . esc_html($last_name) . '</strong> - ' . esc_html($address) . ' - ' . esc_html($phone) . ' - ' . esc_html($email);
        echo ' <a href="?page=gym-parents&delete_parent=' . $parent->ID . '">Delete</a>';

        $athletes = get_post_meta($parent->ID, '_gm_parent_athletes', true);
        if ($athletes) {
            echo '<ul>';
            foreach ($athletes as $athlete_id => $athlete) {
                echo '<li>' . esc_html($athlete['first_name']) . ' ' . esc_html($athlete['last_name']) . ' - ' . esc_html($athlete['gender']) . ' - ' . esc_html($athlete['dob']) . ' - ' . esc_html($athlete['allergies']) . ' - ' . esc_html($athlete['medical_info']) . '</li>';
            }
            echo '</ul>';
        }

        echo '</li>';
    }
    echo '</ul>';
}
?>