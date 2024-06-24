<?php
function gm_coaches_page() {
    global $wpdb;

    echo '<h1>Coaches Management</h1>';

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
            .gm-delete-coach-btn {
                background-color: red;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
          </style>';

    // Fetch all coaches
    $coaches = get_posts(array(
        'post_type' => 'gm_coach',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));

    echo '<div class="gm-tiles-container">';
    foreach ($coaches as $coach) {
        $first_name = get_post_meta($coach->ID, '_gm_coach_first_name', true);
        $last_name = get_post_meta($coach->ID, '_gm_coach_last_name', true);

        echo '<div class="gm-dashboard-tile gm-coach-tile" data-coach-id="' . esc_attr($coach->ID) . '">';
        echo esc_html($first_name) . ' ' . esc_html($last_name);
        echo '</div>';
    }
    echo '<div class="gm-dashboard-tile" id="gm-add-coach-btn">+</div>';
    echo '</div>';

    // Add Coach Modal
    echo '<div class="gm-modal" id="gm-add-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Add New Coach</h2>
                <form id="gm-add-coach-form" method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="gm_add_coach">
                    <input type="hidden" id="gm-coach-id" name="coach_id" value="">
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
                <form id="gm-edit-coach-form" method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="gm_edit_coach">
                    <input type="hidden" id="gm-edit-coach-id" name="coach_id" value="">
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
                <button id="gm-delete-coach-btn" class="gm-delete-coach-btn">Delete Coach</button>
            </div>
          </div>';

    echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const addModal = document.getElementById("gm-add-modal");
                const editModal = document.getElementById("gm-edit-modal");
                const closeModalBtns = document.querySelectorAll(".gm-modal-close");
                const addCoachBtn = document.getElementById("gm-add-coach-btn");
                const deleteCoachBtn = document.getElementById("gm-delete-coach-btn");

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

                const coachTiles = document.querySelectorAll(".gm-coach-tile");
                coachTiles.forEach(tile => {
                    tile.addEventListener("click", function() {
                        const coachId = this.getAttribute("data-coach-id");
                        document.getElementById("gm-edit-coach-id").value = coachId;

                        fetch("' . admin_url('admin-ajax.php') . '", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "action=gm_get_coach_details&coach_id=" + coachId
                        }).then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  document.getElementById("gm-edit-first-name").value = data.first_name;
                                  document.getElementById("gm-edit-last-name").value = data.last_name;
                                  document.getElementById("gm-edit-phone").value = data.phone;
                                  document.getElementById("gm-edit-email").value = data.email;

                                  editModal.style.display = "flex";
                              } else {
                                  console.error("Failed to load coach details");
                              }
                          });
                    });
                });

                deleteCoachBtn.addEventListener("click", function() {
                    const coachId = document.getElementById("gm-edit-coach-id").value;
                    if (confirm("Are you sure you want to delete this coach?")) {
                        fetch("' . admin_url('admin-post.php') . '", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "action=gm_delete_coach&coach_id=" + coachId + "&_wpnonce=' . wp_create_nonce('delete_coach_nonce') . '"
                        }).then(response => response.text())
                          .then(data => {
                              if (data === "success") {
                                  location.reload();
                              } else {
                                  console.error("Failed to delete coach");
                              }
                          });
                    }
                });
            });
          </script>';
}

// Handle adding a new coach
function gm_add_coach() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['phone']) && isset($_POST['email'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);

        $coach_id = wp_insert_post(array(
            'post_title' => $first_name . ' ' . $last_name,
            'post_type' => 'gm_coach',
            'post_status' => 'publish',
            'meta_input' => array(
                '_gm_coach_first_name' => $first_name,
                '_gm_coach_last_name' => $last_name,
                '_gm_coach_phone' => $phone,
                '_gm_coach_email' => $email,
                '_gm_coach_id' => uniqid('coach_', true) // Generate unique ID for the coach
            ),
        ));
    }

    wp_redirect(admin_url('admin.php?page=gym-coaches'));
    exit;
}
add_action('admin_post_gm_add_coach', 'gm_add_coach');

// Handle editing a coach
function gm_edit_coach() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['coach_id']) && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['phone']) && isset($_POST['email'])) {
        $coach_id = intval($_POST['coach_id']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);

        wp_update_post(array(
            'ID' => $coach_id,
            'post_title' => $first_name . ' ' . $last_name,
        ));

        update_post_meta($coach_id, '_gm_coach_first_name', $first_name);
        update_post_meta($coach_id, '_gm_coach_last_name', $last_name);
        update_post_meta($coach_id, '_gm_coach_phone', $phone);
        update_post_meta($coach_id, '_gm_coach_email', $email);
    }

    wp_redirect(admin_url('admin.php?page=gym-coaches'));
    exit;
}
add_action('admin_post_gm_edit_coach', 'gm_edit_coach');

// Handle deleting a coach
function gm_delete_coach() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['coach_id']) && check_admin_referer('delete_coach_nonce')) {
        $coach_id = intval($_POST['coach_id']);
        wp_delete_post($coach_id, true);
        echo 'success';
    } else {
        echo 'failure';
    }

    exit;
}
add_action('admin_post_gm_delete_coach', 'gm_delete_coach');

// Handle getting coach details for editing
function gm_get_coach_details() {
    if (!current_user_can('manage_options')) {
        echo json_encode(['success' => false]);
        wp_die();
    }

    if (isset($_POST['coach_id'])) {
        $coach_id = intval($_POST['coach_id']);
        $first_name = get_post_meta($coach_id, '_gm_coach_first_name', true);
        $last_name = get_post_meta($coach_id, '_gm_coach_last_name', true);
        $phone = get_post_meta($coach_id, '_gm_coach_phone', true);
        $email = get_post_meta($coach_id, '_gm_coach_email', true);

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
add_action('wp_ajax_gm_get_coach_details', 'gm_get_coach_details');
?>
