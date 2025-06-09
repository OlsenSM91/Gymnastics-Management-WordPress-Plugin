<?php
function gm_classes_page() {
    global $wpdb;

    // Fetch all levels
    $levels = get_terms(array(
        'taxonomy' => 'gm_level',
        'hide_empty' => false,
    ));

    // Check if a level is selected
    $selected_level = isset($_GET['level_id']) ? intval($_GET['level_id']) : 0;

    if ($selected_level) {
        // Fetch all classes for the selected level
        $classes = get_posts(array(
            'post_type' => 'gm_class',
            'post_status' => 'publish',
            'numberposts' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'gm_level',
                    'field' => 'term_id',
                    'terms' => $selected_level,
                ),
            ),
        ));
    }

    echo '<h1>Class Management</h1>';


    if ($selected_level) {
        echo '<h2>Classes for Level: ' . get_term($selected_level)->name . '</h2>';
        echo '<a href="' . admin_url('admin.php?page=gym-classes') . '" class="gm-dashboard-tile">Back to Levels</a>';
        echo '<a href="#" class="gm-dashboard-tile" id="gm-add-class-btn">+</a>';

        echo '<div class="gm-tiles-container">';
        foreach ($classes as $class) {
            echo '<div class="gm-class-details">';
            echo '<h2>' . esc_html($class->post_title) . ' <button class="gm-edit-class-btn" data-class-id="' . esc_attr($class->ID) . '">Edit</button></h2>';

            $schedule = get_post_meta($class->ID, '_gm_class_schedule', true);
            if ($schedule) {
                echo '<div class="gm-schedule-container">';
                echo '<h3>Schedule</h3>';
                echo '<div class="gm-calendar">';
                $days_of_week = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                foreach ($days_of_week as $day) {
                    echo '<div class="gm-calendar-day">';
                    echo '<strong>' . $day . '</strong>';
                    foreach ($schedule as $schedule_item) {
                        if (strtolower($day) == $schedule_item['day']) {
                            $start_time = date("g:i A", strtotime($schedule_item['start_time']));
                            $end_time = date("g:i A", strtotime($schedule_item['end_time']));
                            echo '<div class="gm-calendar-event">' . $start_time . ' - ' . $end_time . ' (' . ucfirst($schedule_item['frequency']) . ')</div>';
                        }
                    }
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }

            // Fetch available slots and assigned athletes
            $available_slots = get_post_meta($class->ID, '_gm_class_available_slots', true);
            $assigned_athletes = get_post_meta($class->ID, '_gm_class_athletes', true);
            if (!is_array($assigned_athletes)) {
                $assigned_athletes = array();
            }
            $remaining_seats = $available_slots - count($assigned_athletes);

            echo '<div class="gm-class-availability">';
            echo '<h3>Seats</h3>';
            echo '<p>Available Seats: ' . esc_html($available_slots) . '</p>';
            echo '<p>Remaining Seats: ' . esc_html($remaining_seats) . '</p>';
            echo '</div>';

            // Fetch assigned coaches
            $assigned_coaches = get_post_meta($class->ID, '_gm_class_coaches', true);
            echo '<div class="gm-assign-coaches">';
            echo '<h3>Assigned Coaches</h3>';
            if ($assigned_coaches && !empty($assigned_coaches)) {
                echo '<ul>';
                foreach ($assigned_coaches as $coach_id) {
                    $coach = get_post($coach_id);
                    if ($coach) {
                        echo '<li>' . esc_html($coach->post_title) . ' <button class="gm-remove-coach" data-coach-id="' . esc_attr($coach_id) . '" data-class-id="' . esc_attr($class->ID) . '">&times;</button></li>';
                    } else {
                        error_log('Coach not found: ' . $coach_id); // Debugging line
                    }
                }
                echo '</ul>';
            } else {
                echo '<p>No coaches assigned.</p>';
            }

            echo '<button class="gm-assign-coach-btn" data-class-id="' . esc_attr($class->ID) . '">Assign Coach</button>';
            echo '</div>';

            echo '<div class="gm-assign-athletes">';
            echo '<h3>Assigned Athletes</h3>';
            if ($assigned_athletes && !empty($assigned_athletes)) {
                echo '<ul>';
                foreach ($assigned_athletes as $athlete_id) {
                    // Fetch athlete details using the athlete ID from the parent meta
                    $athlete = null;
                    $parents = get_posts(array(
                        'post_type' => 'gm_parent',
                        'post_status' => 'publish',
                        'numberposts' => -1,
                    ));
                    foreach ($parents as $parent) {
                        $parent_athletes = get_post_meta($parent->ID, '_gm_parent_athletes', true);
                        if (isset($parent_athletes[$athlete_id])) {
                            $athlete = $parent_athletes[$athlete_id];
                            break;
                        }
                    }
                    if ($athlete) {
                        echo '<li>' . esc_html($athlete['first_name']) . ' ' . esc_html($athlete['last_name']) . ' <button class="gm-remove-athlete" data-athlete-id="' . esc_attr($athlete_id) . '" data-class-id="' . esc_attr($class->ID) . '">&times;</button></li>';
                    } else {
                        echo '<li>Unknown Athlete (ID: ' . esc_html($athlete_id) . ')</li>';
                        error_log('Athlete not found: ' . $athlete_id); // Debugging line
                    }
                }
                echo '</ul>';
            } else {
                echo '<p>No athletes assigned.</p>';
            }

            echo '<button class="gm-assign-athlete-btn" data-class-id="' . esc_attr($class->ID) . '">Assign Athlete</button>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<h2>Select a Level</h2>';
        echo '<div class="gm-dashboard-tiles">';
        foreach ($levels as $level) {
            echo '<a href="' . admin_url('admin.php?page=gym-classes&level_id=' . $level->term_id) . '" class="gm-dashboard-tile">' . esc_html($level->name) . '</a>';
        }
        echo '</div>';
    }

    // Add modal
    echo '<div class="gm-modal" id="gm-add-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Add New Class</h2>
                <form id="gm-add-class-form" method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="gm_add_class">
                    <input type="hidden" name="level_id" value="' . $selected_level . '">
                    <label for="gm-add-class-name">Class Name</label>
                    <input type="text" id="gm-add-class-name" name="class_name" required>
                    <br><label for="gm-add-available-seats">Available Seats</label>
                    <input type="number" id="gm-add-available-seats" name="available_slots" required>
                    <br><label for="gm-add-class-price">Class Price</label>
                    <input type="number" id="gm-add-class-price" name="class_price" required>
                    <br><h3>Schedule</h3>
                    <div id="gm-add-schedule-container"></div>
                    <button type="button" id="gm-add-schedule">Add Another Day</button>
                    <br><input type="submit" value="Add Class">
                </form>
            </div>
          </div>';

    // Edit modal
    echo '<div class="gm-modal" id="gm-edit-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Edit Class</h2>
                <form id="gm-edit-class-form" method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="gm_edit_class">
                    <input type="hidden" id="gm-edit-class-id" name="class_id" value="">
                    <label for="gm-edit-class-name">Class Name</label>
                    <input type="text" id="gm-edit-class-name" name="class_name" required>
                    <br><label for="gm-edit-available-seats">Available Seats</label>
                    <input type="number" id="gm-edit-available-seats" name="available_slots" required>
                    <br><label for="gm-edit-class-price">Class Price</label>
                    <input type="number" id="gm-edit-class-price" name="class_price" required>
                    <br><h3>Schedule</h3>
                    <div id="gm-edit-schedule-container"></div>
                    <button type="button" id="gm-add-edit-schedule">Add Another Day</button>
                    <br><input type="submit" value="Update Class">
                </form>
                <button id="gm-delete-class-btn" class="gm-delete-class-btn">Delete Class</button>
            </div>
          </div>';

    // Assign athletes modal
    echo '<div class="gm-modal" id="gm-assign-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Select Athletes to Assign</h2>
                <form method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="gm_assign_athletes_bulk">
                    <input type="hidden" id="gm-modal-class-id" name="class_id" value="">
                    <input type="hidden" name="level_id" value="' . $selected_level . '">
                    <div id="gm-assign-athletes-list">';
    $athletes = get_posts(array(
        'post_type' => 'gm_parent',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($athletes as $parent) {
        $parent_athletes = get_post_meta($parent->ID, '_gm_parent_athletes', true);
        if ($parent_athletes) {
            foreach ($parent_athletes as $athlete_id => $athlete) {
                echo '<div>
                        <input type="checkbox" name="athlete_ids[]" value="' . esc_attr($athlete_id) . '">
                        ' . esc_html($athlete['first_name']) . ' ' . esc_html($athlete['last_name']) . ' (Parent: ' . esc_html($parent->post_title) . ')
                      </div>';
            }
        }
    }
    echo '        </div>
                    <br><input type="submit" value="Assign Selected Athletes">
                </form>
            </div>
          </div>';

    // Assign coaches modal
    echo '<div class="gm-modal" id="gm-assign-coach-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Select Coaches to Assign</h2>
                <form method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="gm_assign_coaches_bulk">
                    <input type="hidden" id="gm-modal-class-id-coach" name="class_id" value="">
                    <input type="hidden" name="level_id" value="' . $selected_level . '">
                    <div id="gm-assign-coaches-list">';
    $coaches = get_posts(array(
        'post_type' => 'gm_coach',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($coaches as $coach) {
        echo '<div>
                <input type="checkbox" name="coach_ids[]" value="' . esc_attr($coach->ID) . '">
                ' . esc_html($coach->post_title) . '
              </div>';
    }
    echo '        </div>
                    <br><input type="submit" value="Assign Selected Coaches">
                </form>
            </div>
          </div>';

}

// Handle adding a new class
function gm_add_class() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_name']) && isset($_POST['level_id'])) {
        $class_name = sanitize_text_field($_POST['class_name']);
        $available_slots = intval($_POST['available_slots']);
        $class_price = floatval($_POST['class_price']);
        $class_schedule = isset($_POST['class_schedule']) ? $_POST['class_schedule'] : array();
        $level_id = intval($_POST['level_id']);

        $class_id = wp_insert_post(array(
            'post_title' => $class_name,
            'post_type' => 'gm_class',
            'post_status' => 'publish',
            'meta_input' => array(
                '_gm_class_level' => $level_id,
                '_gm_class_available_slots' => $available_slots,
                '_gm_class_price' => $class_price,
                '_gm_class_schedule' => $class_schedule,
            ),
        ));

        if ($class_id) {
            wp_set_object_terms($class_id, $level_id, 'gm_level');
        }
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_gm_add_class', 'gm_add_class');

// Handle editing a class
function gm_edit_class() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['class_name'])) {
        $class_id = intval($_POST['class_id']);
        $class_name = sanitize_text_field($_POST['class_name']);
        $available_slots = intval($_POST['available_slots']);
        $class_price = floatval($_POST['class_price']);
        $class_schedule = isset($_POST['class_schedule']) ? $_POST['class_schedule'] : array();

        wp_update_post(array(
            'ID' => $class_id,
            'post_title' => $class_name,
        ));

        update_post_meta($class_id, '_gm_class_available_slots', $available_slots);
        update_post_meta($class_id, '_gm_class_price', $class_price);
        update_post_meta($class_id, '_gm_class_schedule', $class_schedule);
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_gm_edit_class', 'gm_edit_class');

// Handle deleting a class
function gm_delete_class() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && check_admin_referer('delete_class_nonce')) {
        $class_id = intval($_POST['class_id']);
        wp_delete_post($class_id, true);
        echo 'success';
    } else {
        echo 'failure';
    }

    exit;
}
add_action('admin_post_gm_delete_class', 'gm_delete_class');

// Handle getting class details for editing
function gm_get_class_details() {
    if (!current_user_can('manage_options')) {
        echo json_encode(['success' => false]);
        wp_die();
    }

    if (isset($_POST['class_id'])) {
        $class_id = intval($_POST['class_id']);
        $class = get_post($class_id);
        $class_name = $class->post_title;
        $available_slots = get_post_meta($class_id, '_gm_class_available_slots', true);
        $class_price = get_post_meta($class_id, '_gm_class_price', true);
        $class_schedule = get_post_meta($class_id, '_gm_class_schedule', true);

        echo json_encode([
            'success' => true,
            'class_name' => $class_name,
            'available_slots' => $available_slots,
            'class_price' => $class_price,
            'class_schedule' => $class_schedule
        ]);
    } else {
        echo json_encode(['success' => false]);
    }

    wp_die();
}
add_action('wp_ajax_gm_get_class_details', 'gm_get_class_details');

// Handle athlete assignment
function gm_assign_athlete() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['athlete_id']) && isset($_POST['level_id'])) {
        $class_id = intval($_POST['class_id']);
        $athlete_id = sanitize_text_field($_POST['athlete_id']);
        $assigned_athletes = get_post_meta($class_id, '_gm_class_athletes', true);
        if (!is_array($assigned_athletes)) {
            $assigned_athletes = array();
        }

        if (!in_array($athlete_id, $assigned_athletes)) {
            $assigned_athletes[] = $athlete_id;
            update_post_meta($class_id, '_gm_class_athletes', $assigned_athletes);
        }
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_gm_assign_athlete', 'gm_assign_athlete');

// Handle bulk athlete assignment
function gm_assign_athletes_bulk() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['athlete_ids'])) {
        $class_id = intval($_POST['class_id']);
        $athlete_ids = $_POST['athlete_ids'];
        $assigned_athletes = get_post_meta($class_id, '_gm_class_athletes', true);
        if (!is_array($assigned_athletes)) {
            $assigned_athletes = array();
        }

        foreach ($athlete_ids as $athlete_id) {
            if (!in_array($athlete_id, $assigned_athletes)) {
                $assigned_athletes[] = $athlete_id;
            }
        }

        update_post_meta($class_id, '_gm_class_athletes', $assigned_athletes);
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_gm_assign_athletes_bulk', 'gm_assign_athletes_bulk');

// Handle athlete removal
function gm_remove_athlete() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['athlete_id'])) {
        $class_id = intval($_POST['class_id']);
        $athlete_id = sanitize_text_field($_POST['athlete_id']);
        $assigned_athletes = get_post_meta($class_id, '_gm_class_athletes', true);
        if (!is_array($assigned_athletes)) {
            $assigned_athletes = array();
        }

        if (($key = array_search($athlete_id, $assigned_athletes)) !== false) {
            unset($assigned_athletes[$key]);
            update_post_meta($class_id, '_gm_class_athletes', array_values($assigned_athletes));
            echo 'success';
        } else {
            echo 'failure';
        }
    } else {
        echo 'failure';
    }

    exit;
}
add_action('admin_post_gm_remove_athlete', 'gm_remove_athlete');

// Handle coach assignment
function gm_assign_coach() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['coach_id']) && isset($_POST['level_id'])) {
        $class_id = intval($_POST['class_id']);
        $coach_id = sanitize_text_field($_POST['coach_id']);
        $assigned_coaches = get_post_meta($class_id, '_gm_class_coaches', true);
        if (!is_array($assigned_coaches)) {
            $assigned_coaches = array();
        }

        if (!in_array($coach_id, $assigned_coaches)) {
            $assigned_coaches[] = $coach_id;
            update_post_meta($class_id, '_gm_class_coaches', $assigned_coaches);
        }
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_gm_assign_coach', 'gm_assign_coach');

// Handle bulk coach assignment
function gm_assign_coaches_bulk() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['coach_ids'])) {
        $class_id = intval($_POST['class_id']);
        $coach_ids = $_POST['coach_ids'];
        $assigned_coaches = get_post_meta($class_id, '_gm_class_coaches', true);
        if (!is_array($assigned_coaches)) {
            $assigned_coaches = array();
        }

        foreach ($coach_ids as $coach_id) {
            if (!in_array($coach_id, $assigned_coaches)) {
                $assigned_coaches[] = $coach_id;
            }
        }

        update_post_meta($class_id, '_gm_class_coaches', $assigned_coaches);
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_gm_assign_coaches_bulk', 'gm_assign_coaches_bulk');

// Handle coach removal
function gm_remove_coach() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['coach_id'])) {
        $class_id = intval($_POST['class_id']);
        $coach_id = sanitize_text_field($_POST['coach_id']);
        $assigned_coaches = get_post_meta($class_id, '_gm_class_coaches', true);
        if (!is_array($assigned_coaches)) {
            $assigned_coaches = array();
        }

        if (($key = array_search($coach_id, $assigned_coaches)) !== false) {
            unset($assigned_coaches[$key]);
            update_post_meta($class_id, '_gm_class_coaches', array_values($assigned_coaches));
            echo 'success';
        } else {
            echo 'failure';
        }
    } else {
        echo 'failure';
    }

    exit;
}
add_action('admin_post_gm_remove_coach', 'gm_remove_coach');
?>
