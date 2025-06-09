<?php
function wsm_sessions_page() {
    global $wpdb;

    // Fetch all levels
    $levels = get_terms(array(
        'taxonomy' => 'wsm_level',
        'hide_empty' => false,
    ));

    // Check if a level is selected
    $selected_level = isset($_GET['level_id']) ? intval($_GET['level_id']) : 0;

    if ($selected_level) {
        // Fetch all classes for the selected level
        $classes = get_posts(array(
            'post_type' => 'wsm_session',
            'post_status' => 'publish',
            'numberposts' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'wsm_level',
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

            $schedule = get_post_meta($class->ID, '_wsm_session_schedule', true);
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
            $available_slots = get_post_meta($class->ID, '_wsm_session_available_slots', true);
            $assigned_athletes = get_post_meta($class->ID, '_wsm_session_athletes', true);
            if (!is_array($assigned_athletes)) {
                $assigned_athletes = array();
            }
            $remaining_seats = $available_slots - count($assigned_athletes);

            echo '<div class="gm-class-availability">';
            echo '<h3>Seats</h3>';
            echo '<p>Available Seats: ' . esc_html($available_slots) . '</p>';
            echo '<p>Remaining Seats: ' . esc_html($remaining_seats) . '</p>';
            echo '</div>';

            // Fetch assigned instructors
            $assigned_instructors = get_post_meta($class->ID, '_wsm_session_instructors', true);
            echo '<div class="gm-assign-instructors">';
            echo '<h3>Assigned Instructors</h3>';
            if ($assigned_instructors && !empty($assigned_instructors)) {
                echo '<ul>';
                foreach ($assigned_instructors as $instructor_id) {
                    $instructor = get_post($instructor_id);
                    if ($instructor) {
                        echo '<li>' . esc_html($instructor->post_title) . ' <button class="gm-remove-instructor" data-instructor-id="' . esc_attr($instructor_id) . '" data-class-id="' . esc_attr($class->ID) . '">&times;</button></li>';
                    } else {
                        error_log('Coach not found: ' . $instructor_id); // Debugging line
                    }
                }
                echo '</ul>';
            } else {
                echo '<p>No instructors assigned.</p>';
            }

            echo '<button class="gm-assign-instructor-btn" data-class-id="' . esc_attr($class->ID) . '">Assign Coach</button>';
            echo '</div>';

            echo '<div class="gm-assign-athletes">';
            echo '<h3>Assigned Athletes</h3>';
            if ($assigned_athletes && !empty($assigned_athletes)) {
                echo '<ul>';
                foreach ($assigned_athletes as $athlete_id) {
                    // Fetch athlete details using the athlete ID from the parent meta
                    $athlete = null;
                    $parents = get_posts(array(
                        'post_type' => 'wsm_parent',
                        'post_status' => 'publish',
                        'numberposts' => -1,
                    ));
                    foreach ($parents as $parent) {
                        $parent_athletes = get_post_meta($parent->ID, '_wsm_parent_athletes', true);
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
                    <input type="hidden" name="action" value="wsm_add_session">
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
                    <input type="hidden" name="action" value="wsm_edit_session">
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
                    <input type="hidden" name="action" value="wsm_assign_athletes_bulk">
                    <input type="hidden" id="gm-modal-class-id" name="class_id" value="">
                    <input type="hidden" name="level_id" value="' . $selected_level . '">
                    <div id="gm-assign-athletes-list">';
    $athletes = get_posts(array(
        'post_type' => 'wsm_parent',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($athletes as $parent) {
        $parent_athletes = get_post_meta($parent->ID, '_wsm_parent_athletes', true);
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

    // Assign instructors modal
    echo '<div class="gm-modal" id="gm-assign-instructor-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Select Instructors to Assign</h2>
                <form method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="wsm_assign_instructors_bulk">
                    <input type="hidden" id="gm-modal-class-id-instructor" name="class_id" value="">
                    <input type="hidden" name="level_id" value="' . $selected_level . '">
                    <div id="gm-assign-instructors-list">';
    $instructors = get_posts(array(
        'post_type' => 'wsm_instructor',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($instructors as $instructor) {
        echo '<div>
                <input type="checkbox" name="instructor_ids[]" value="' . esc_attr($instructor->ID) . '">
                ' . esc_html($instructor->post_title) . '
              </div>';
    }
    echo '        </div>
                    <br><input type="submit" value="Assign Selected Instructors">
                </form>
            </div>
          </div>';

}

// Handle adding a new class
function wsm_add_session() {
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
            'post_type' => 'wsm_session',
            'post_status' => 'publish',
            'meta_input' => array(
                '_wsm_session_level' => $level_id,
                '_wsm_session_available_slots' => $available_slots,
                '_wsm_session_price' => $class_price,
                '_wsm_session_schedule' => $class_schedule,
            ),
        ));

        if ($class_id) {
            wp_set_object_terms($class_id, $level_id, 'wsm_level');
        }
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_wsm_add_session', 'wsm_add_session');

// Handle editing a class
function wsm_edit_session() {
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

        update_post_meta($class_id, '_wsm_session_available_slots', $available_slots);
        update_post_meta($class_id, '_wsm_session_price', $class_price);
        update_post_meta($class_id, '_wsm_session_schedule', $class_schedule);
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_wsm_edit_session', 'wsm_edit_session');

// Handle deleting a class
function wsm_delete_session() {
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
add_action('admin_post_wsm_delete_session', 'wsm_delete_session');

// Handle getting class details for editing
function wsm_get_session_details() {
    if (!current_user_can('manage_options')) {
        echo json_encode(['success' => false]);
        wp_die();
    }

    if (isset($_POST['class_id'])) {
        $class_id = intval($_POST['class_id']);
        $class = get_post($class_id);
        $class_name = $class->post_title;
        $available_slots = get_post_meta($class_id, '_wsm_session_available_slots', true);
        $class_price = get_post_meta($class_id, '_wsm_session_price', true);
        $class_schedule = get_post_meta($class_id, '_wsm_session_schedule', true);

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
add_action('wp_ajax_wsm_get_session_details', 'wsm_get_session_details');

// Handle athlete assignment
function wsm_assign_athlete() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['athlete_id']) && isset($_POST['level_id'])) {
        $class_id = intval($_POST['class_id']);
        $athlete_id = sanitize_text_field($_POST['athlete_id']);
        $assigned_athletes = get_post_meta($class_id, '_wsm_session_athletes', true);
        if (!is_array($assigned_athletes)) {
            $assigned_athletes = array();
        }

        if (!in_array($athlete_id, $assigned_athletes)) {
            $assigned_athletes[] = $athlete_id;
            update_post_meta($class_id, '_wsm_session_athletes', $assigned_athletes);
        }
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_wsm_assign_athlete', 'wsm_assign_athlete');

// Handle bulk athlete assignment
function wsm_assign_athletes_bulk() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['athlete_ids'])) {
        $class_id = intval($_POST['class_id']);
        $athlete_ids = $_POST['athlete_ids'];
        $assigned_athletes = get_post_meta($class_id, '_wsm_session_athletes', true);
        if (!is_array($assigned_athletes)) {
            $assigned_athletes = array();
        }

        foreach ($athlete_ids as $athlete_id) {
            if (!in_array($athlete_id, $assigned_athletes)) {
                $assigned_athletes[] = $athlete_id;
            }
        }

        update_post_meta($class_id, '_wsm_session_athletes', $assigned_athletes);
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_wsm_assign_athletes_bulk', 'wsm_assign_athletes_bulk');

// Handle athlete removal
function wsm_remove_athlete() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['athlete_id'])) {
        $class_id = intval($_POST['class_id']);
        $athlete_id = sanitize_text_field($_POST['athlete_id']);
        $assigned_athletes = get_post_meta($class_id, '_wsm_session_athletes', true);
        if (!is_array($assigned_athletes)) {
            $assigned_athletes = array();
        }

        if (($key = array_search($athlete_id, $assigned_athletes)) !== false) {
            unset($assigned_athletes[$key]);
            update_post_meta($class_id, '_wsm_session_athletes', array_values($assigned_athletes));
            echo 'success';
        } else {
            echo 'failure';
        }
    } else {
        echo 'failure';
    }

    exit;
}
add_action('admin_post_wsm_remove_athlete', 'wsm_remove_athlete');

// Handle instructor assignment
function wsm_assign_instructor() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['instructor_id']) && isset($_POST['level_id'])) {
        $class_id = intval($_POST['class_id']);
        $instructor_id = sanitize_text_field($_POST['instructor_id']);
        $assigned_instructors = get_post_meta($class_id, '_wsm_session_instructors', true);
        if (!is_array($assigned_instructors)) {
            $assigned_instructors = array();
        }

        if (!in_array($instructor_id, $assigned_instructors)) {
            $assigned_instructors[] = $instructor_id;
            update_post_meta($class_id, '_wsm_session_instructors', $assigned_instructors);
        }
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_wsm_assign_instructor', 'wsm_assign_instructor');

// Handle bulk instructor assignment
function wsm_assign_instructors_bulk() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['instructor_ids'])) {
        $class_id = intval($_POST['class_id']);
        $instructor_ids = $_POST['instructor_ids'];
        $assigned_instructors = get_post_meta($class_id, '_wsm_session_instructors', true);
        if (!is_array($assigned_instructors)) {
            $assigned_instructors = array();
        }

        foreach ($instructor_ids as $instructor_id) {
            if (!in_array($instructor_id, $assigned_instructors)) {
                $assigned_instructors[] = $instructor_id;
            }
        }

        update_post_meta($class_id, '_wsm_session_instructors', $assigned_instructors);
    }

    wp_redirect(admin_url('admin.php?page=gym-classes&level_id=' . intval($_POST['level_id'])));
    exit;
}
add_action('admin_post_wsm_assign_instructors_bulk', 'wsm_assign_instructors_bulk');

// Handle instructor removal
function wsm_remove_instructor() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['class_id']) && isset($_POST['instructor_id'])) {
        $class_id = intval($_POST['class_id']);
        $instructor_id = sanitize_text_field($_POST['instructor_id']);
        $assigned_instructors = get_post_meta($class_id, '_wsm_session_instructors', true);
        if (!is_array($assigned_instructors)) {
            $assigned_instructors = array();
        }

        if (($key = array_search($instructor_id, $assigned_instructors)) !== false) {
            unset($assigned_instructors[$key]);
            update_post_meta($class_id, '_wsm_session_instructors', array_values($assigned_instructors));
            echo 'success';
        } else {
            echo 'failure';
        }
    } else {
        echo 'failure';
    }

    exit;
}
add_action('admin_post_wsm_remove_instructor', 'wsm_remove_instructor');
?>
