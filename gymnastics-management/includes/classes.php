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

    echo '<style>
            .gm-dashboard-tile {
                display: inline-block;
                width: 200px;
                margin: 10px;
                padding: 20px;
                text-align: center;
                background-color: #f1f1f1;
                border: 1px solid #ccc;
                border-radius: 4px;
                text-decoration: none;
                color: #000;
                font-weight: bold;
            }
            .gm-dashboard-tile:hover {
                background-color: #e1e1e1;
            }
            .gm-tiles-container {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
            }
            .gm-class-details, .gm-class-schedule, .gm-assign-athletes, .gm-class-availability {
                flex: 1 1 calc(33.333% - 20px);
                margin: 10px;
                padding: 20px;
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .gm-remove-athlete {
                background-color: red;
                color: white;
                border: none;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                cursor: pointer;
                text-align: center;
                line-height: 20px;
            }
            .gm-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                justify-content: center;
                align-items: center;
            }
            .gm-modal-content {
                background-color: white;
                padding: 20px;
                border-radius: 4px;
                width: 80%;
                max-width: 500px;
            }
            .gm-modal-close {
                float: right;
                cursor: pointer;
                font-size: 20px;
            }
          </style>';

    if ($selected_level) {
        echo '<h2>Classes for Level: ' . get_term($selected_level)->name . '</h2>';
        echo '<a href="' . admin_url('admin.php?page=gym-classes') . '" class="gm-dashboard-tile">Back to Levels</a>';
        echo '<a href="' . admin_url('admin.php?page=gym-classes&level_id=' . $selected_level . '&action=add_class') . '" class="gm-dashboard-tile">+</a>';

        if (isset($_GET['action']) && $_GET['action'] === 'add_class') {
            gm_add_class_form($selected_level);
        }

        echo '<div class="gm-tiles-container">';
        foreach ($classes as $class) {
            echo '<div class="gm-class-details">';
            echo '<h2>' . esc_html($class->post_title) . '</h2>';

            $schedule = get_post_meta($class->ID, '_gm_class_schedule', true);
            if ($schedule) {
                echo '<div class="gm-class-schedule">';
                echo '<h3>Schedule</h3>';
                echo '<ul>';
                foreach ($schedule as $schedule_item) {
                    echo '<li>' . ucfirst($schedule_item['day']) . ': ' . $schedule_item['start_time'] . ' - ' . $schedule_item['end_time'] . ' (' . ucfirst($schedule_item['frequency']) . ')</li>';
                }
                echo '</ul>';
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

    echo '<div class="gm-modal" id="gm-assign-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Select Athletes to Assign</h2>
                <form method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="gm_assign_athletes_bulk">
                    <input type="hidden" id="gm-modal-class-id" name="class_id" value="">
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

    echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const assignBtns = document.querySelectorAll(".gm-assign-athlete-btn");
                const modal = document.getElementById("gm-assign-modal");
                const closeModal = document.querySelector(".gm-modal-close");

                assignBtns.forEach(btn => {
                    btn.addEventListener("click", function() {
                        const classId = this.getAttribute("data-class-id");
                        document.getElementById("gm-modal-class-id").value = classId;
                        modal.style.display = "flex";
                    });
                });

                closeModal.addEventListener("click", function() {
                    modal.style.display = "none";
                });

                window.addEventListener("click", function(event) {
                    if (event.target === modal) {
                        modal.style.display = "none";
                    }
                });

                const removeBtns = document.querySelectorAll(".gm-remove-athlete");
                removeBtns.forEach(btn => {
                    btn.addEventListener("click", function() {
                        const athleteId = this.getAttribute("data-athlete-id");
                        const classId = this.getAttribute("data-class-id");

                        fetch("' . admin_url('admin-post.php') . '", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "action=gm_remove_athlete&class_id=" + classId + "&athlete_id=" + athleteId
                        }).then(response => response.text())
                          .then(data => {
                              if (data === "success") {
                                  location.reload();
                              } else {
                                  console.error("Failed to remove athlete");
                              }
                          });
                    });
                });
            });
          </script>';
}

// Form to add a new class
function gm_add_class_form($level_id) {
    echo '<h2>Add New Class</h2>';
    echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
    echo '<input type="hidden" name="action" value="gm_add_class">';
    echo '<input type="hidden" name="level_id" value="' . $level_id . '">';
    echo '<label for="class_name">Class Name</label>';
    echo '<input type="text" name="class_name" id="class_name" required>';
    echo '<br><label for="available_slots">Available Seats</label>';
    echo '<input type="number" name="available_slots" id="available_slots" required>';
    echo '<br><label for="class_price">Class Price</label>';
    echo '<input type="number" name="class_price" id="class_price" required>';
    echo '<br><h3>Schedule</h3>';
    echo '<div id="schedule-container">';
    echo '<div class="schedule-row">';
    echo '<select name="class_schedule[0][day]" required>';
    echo '<option value="monday">Monday</option>';
    echo '<option value="tuesday">Tuesday</option>';
    echo '<option value="wednesday">Wednesday</option>';
    echo '<option value="thursday">Thursday</option>';
    echo '<option value="friday">Friday</option>';
    echo '<option value="saturday">Saturday</option>';
    echo '<option value="sunday">Sunday</option>';
    echo '</select>';
    echo '<input type="time" name="class_schedule[0][start_time]" required>';
    echo '<input type="time" name="class_schedule[0][end_time]" required>';
    echo '<select name="class_schedule[0][frequency]" required>';
    echo '<option value="daily">Daily</option>';
    echo '<option value="weekly">Weekly</option>';
    echo '<option value="monthly">Monthly</option>';
    echo '</select>';
    echo '<button type="button" class="remove-schedule">-</button>';
    echo '</div>';
    echo '</div>';
    echo '<button type="button" id="add-schedule">Add Another Day</button>';
    echo '<br><input type="submit" value="Add Class">';
    echo '</form>';
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
?>