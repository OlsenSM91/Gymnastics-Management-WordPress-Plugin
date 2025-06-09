<?php
function wsm_instructors_page() {
    global $wpdb;

    echo '<h1>Instructors Management</h1>';

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
            .gm-delete-instructor-btn {
                background-color: red;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
          </style>';

    // Fetch all instructors
    $instructors = get_posts(array(
        'post_type' => 'wsm_instructor',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));

    echo '<div class="gm-tiles-container">';
    foreach ($instructors as $instructor) {
        $first_name = get_post_meta($instructor->ID, '_wsm_instructor_first_name', true);
        $last_name = get_post_meta($instructor->ID, '_wsm_instructor_last_name', true);

        echo '<div class="gm-dashboard-tile gm-instructor-tile" data-instructor-id="' . esc_attr($instructor->ID) . '">';
        echo esc_html($first_name) . ' ' . esc_html($last_name);
        echo '</div>';
    }
    echo '<div class="gm-dashboard-tile" id="gm-add-instructor-btn">+</div>';
    echo '</div>';

    // Add Coach Modal
    echo '<div class="gm-modal" id="gm-add-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Add New Coach</h2>
                <form id="gm-add-instructor-form" method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="wsm_add_instructor">
                    <input type="hidden" id="gm-instructor-id" name="instructor_id" value="">
                    <label for="gm-add-first-name">First Name</label>
                    <input type="text" id="gm-add-first-name" name="first_name" required>
                    <br><label for="gm-add-last-name">Last Name</label>
                    <input type="text" id="gm-add-last-name" name="last_name" required>
                    <br><label for="gm-add-phone">Phone</label>
                    <input type="text" id="gm-add-phone" name="phone" required>
                    <br><label for="gm-add-email">Email</label>
                    <input type="email" id="gm-add-email" name="email" required>
                    <br><input type="submit" value="Add Coach">
                </form>
            </div>
          </div>';

    // Edit Coach Modal
    echo '<div class="gm-modal" id="gm-edit-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Edit Coach</h2>
                <form id="gm-edit-instructor-form" method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="wsm_edit_instructor">
                    <input type="hidden" id="gm-edit-instructor-id" name="instructor_id" value="">
                    <label for="gm-edit-first-name">First Name</label>
                    <input type="text" id="gm-edit-first-name" name="first_name" required>
                    <br><label for="gm-edit-last-name">Last Name</label>
                    <input type="text" id="gm-edit-last-name" name="last_name" required>
                    <br><label for="gm-edit-phone">Phone</label>
                    <input type="text" id="gm-edit-phone" name="phone" required>
                    <br><label for="gm-edit-email">Email</label>
                    <input type="email" id="gm-edit-email" name="email" required>
                    <br><input type="submit" value="Update Coach">
                </form>
                <button id="gm-delete-instructor-btn" class="gm-delete-instructor-btn">Delete Coach</button>
            </div>
          </div>';

    echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const addModal = document.getElementById("gm-add-modal");
                const editModal = document.getElementById("gm-edit-modal");
                const closeModalBtns = document.querySelectorAll(".gm-modal-close");
                const addCoachBtn = document.getElementById("gm-add-instructor-btn");
                const deleteCoachBtn = document.getElementById("gm-delete-instructor-btn");

                addCoachBtn.addEventListener("click", function() {
                    addModal.style.display = "flex";
                });

                closeModalBtns.forEach(btn => {
                    btn.addEventListener("click", function() {
                        addModal.style.display = "none";
                        editModal.style.display = "none";
                    });
                });

                window.addEventListener("click", function(event) {
                    if (event.target === addModal || event.target === editModal) {
                        addModal.style.display = "none";
                        editModal.style.display = "none";
                    }
                });

                const instructorTiles = document.querySelectorAll(".gm-instructor-tile");
                instructorTiles.forEach(tile => {
                    tile.addEventListener("click", function() {
                        const instructorId = this.getAttribute("data-instructor-id");
                        document.getElementById("gm-edit-instructor-id").value = instructorId;

                        fetch("' . admin_url('admin-ajax.php') . '", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "action=wsm_get_instructor_details&instructor_id=" + instructorId
                        }).then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  document.getElementById("gm-edit-first-name").value = data.first_name;
                                  document.getElementById("gm-edit-last-name").value = data.last_name;
                                  document.getElementById("gm-edit-phone").value = data.phone;
                                  document.getElementById("gm-edit-email").value = data.email;

                                  editModal.style.display = "flex";
                              } else {
                                  console.error("Failed to load instructor details");
                              }
                          });
                    });
                });

                deleteCoachBtn.addEventListener("click", function() {
                    const instructorId = document.getElementById("gm-edit-instructor-id").value;
                    if (confirm("Are you sure you want to delete this instructor?")) {
                        fetch("' . admin_url('admin-post.php') . '", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "action=wsm_delete_instructor&instructor_id=" + instructorId + "&_wpnonce=' . wp_create_nonce('delete_instructor_nonce') . '"
                        }).then(response => response.text())
                          .then(data => {
                              if (data === "success") {
                                  location.reload();
                              } else {
                                  console.error("Failed to delete instructor");
                              }
                          });
                    }
                });
            });
          </script>';
}

// Handle adding a new instructor
function wsm_add_instructor() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['phone']) && isset($_POST['email'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);

        // Create the instructor post
        $instructor_id = wp_insert_post(array(
            'post_title' => $first_name . ' ' . $last_name,
            'post_type' => 'wsm_instructor',
            'post_status' => 'publish',
            'meta_input' => array(
                '_wsm_instructor_first_name' => $first_name,
                '_wsm_instructor_last_name' => $last_name,
                '_wsm_instructor_phone' => $phone,
                '_wsm_instructor_email' => $email,
            ),
        ));

        // Create the WordPress user
        $user_id = wp_create_user($email, wp_generate_password(), $email);
        if (!is_wp_error($user_id)) {
            wp_update_user(array(
                'ID' => $user_id,
                'role' => 'instructor',
                'first_name' => $first_name,
                'last_name' => $last_name,
            ));

            // Send email with login details
            wp_send_new_user_notifications($user_id, 'user');

            // Update the instructor post with the user ID
            update_post_meta($instructor_id, '_wsm_instructor_user_id', $user_id);
        }
    }

    wp_redirect(admin_url('admin.php?page=wsm-instructors'));
    exit;
}
add_action('admin_post_wsm_add_instructor', 'wsm_add_instructor');

// Handle editing a instructor
function wsm_edit_instructor() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['instructor_id']) && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['phone']) && isset($_POST['email'])) {
        $instructor_id = intval($_POST['instructor_id']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);

        wp_update_post(array(
            'ID' => $instructor_id,
            'post_title' => $first_name . ' ' . $last_name,
        ));

        update_post_meta($instructor_id, '_wsm_instructor_first_name', $first_name);
        update_post_meta($instructor_id, '_wsm_instructor_last_name', $last_name);
        update_post_meta($instructor_id, '_wsm_instructor_phone', $phone);
        update_post_meta($instructor_id, '_wsm_instructor_email', $email);

        // Update the WordPress user associated with the instructor
        $user_id = get_post_meta($instructor_id, '_wsm_instructor_user_id', true);
        if ($user_id) {
            wp_update_user(array(
                'ID' => $user_id,
                'user_email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ));
        }
    }

    wp_redirect(admin_url('admin.php?page=wsm-instructors'));
    exit;
}
add_action('admin_post_wsm_edit_instructor', 'wsm_edit_instructor');

// Handle deleting a instructor
function wsm_delete_instructor() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['instructor_id']) && check_admin_referer('delete_instructor_nonce')) {
        $instructor_id = intval($_POST['instructor_id']);

        // Delete the WordPress user associated with the instructor
        $user_id = get_post_meta($instructor_id, '_wsm_instructor_user_id', true);
        if ($user_id) {
            wp_delete_user($user_id);
        }

        // Delete the instructor post
        wp_delete_post($instructor_id, true);
        echo 'success';
    } else {
        echo 'failure';
    }

    exit;
}
add_action('admin_post_wsm_delete_instructor', 'wsm_delete_instructor');

// Handle getting instructor details for editing
function wsm_get_instructor_details() {
    if (!current_user_can('manage_options')) {
        echo json_encode(['success' => false]);
        wp_die();
    }

    if (isset($_POST['instructor_id'])) {
        $instructor_id = intval($_POST['instructor_id']);
        $first_name = get_post_meta($instructor_id, '_wsm_instructor_first_name', true);
        $last_name = get_post_meta($instructor_id, '_wsm_instructor_last_name', true);
        $phone = get_post_meta($instructor_id, '_wsm_instructor_phone', true);
        $email = get_post_meta($instructor_id, '_wsm_instructor_email', true);

        echo json_encode([
            'success' => true,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'email' => $email
        ]);
    } else {
        echo json_encode(['success' => false]);
    }

    wp_die();
}
add_action('wp_ajax_wsm_get_instructor_details', 'wsm_get_instructor_details');
?>
