<?php
function wsm_levels_page() {
    global $wpdb;

    // Handle adding a new level
    if (isset($_POST['action']) && $_POST['action'] === 'add_level') {
        $level_name = sanitize_text_field($_POST['level_name']);

        wp_insert_term($level_name, 'wsm_level');
        wp_redirect(admin_url('admin.php?page=gym-levels'));
        exit;
    }

    // Handle editing a level
    if (isset($_POST['action']) && $_POST['action'] === 'edit_level') {
        $level_id = intval($_POST['level_id']);
        $level_name = sanitize_text_field($_POST['level_name']);

        wp_update_term($level_id, 'wsm_level', array(
            'name' => $level_name,
        ));
        wp_redirect(admin_url('admin.php?page=gym-levels'));
        exit;
    }

    // Handle deleting a level
    if (isset($_GET['delete_level'])) {
        $level_id = intval($_GET['delete_level']);
        wp_delete_term($level_id, 'wsm_level');
        wp_redirect(admin_url('admin.php?page=gym-levels'));
        exit;
    }

    // Fetch all levels
    $levels = get_terms(array(
        'taxonomy' => 'wsm_level',
        'hide_empty' => false,
    ));

    echo '<h1>Levels Management</h1>';

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
            .gm-edit-level-btn {
                margin-left: 10px;
                padding: 5px 10px;
                background-color: #0073aa;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            .gm-delete-level-btn {
                margin-left: 10px;
                padding: 5px 10px;
                background-color: red;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
          </style>';

    echo '<div class="gm-tiles-container">';
    foreach ($levels as $level) {
        echo '<div class="gm-dashboard-tile">';
        echo esc_html($level->name);
        echo ' <button class="gm-edit-level-btn" data-level-id="' . esc_attr($level->term_id) . '" data-level-name="' . esc_attr($level->name) . '">Edit</button>';
        echo ' <button class="gm-delete-level-btn" data-level-id="' . esc_attr($level->term_id) . '">Delete</button>';
        echo '</div>';
    }
    echo '<div class="gm-dashboard-tile" id="gm-add-level-tile">+</div>';
    echo '</div>';

    // Add Level Modal
    echo '<div class="gm-modal" id="gm-add-level-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Add New Level</h2>
                <form method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="add_level">
                    <label for="gm-add-level-name">Level Name</label>
                    <input type="text" id="gm-add-level-name" name="level_name" required>
                    <br><input type="submit" value="Add Level">
                </form>
            </div>
          </div>';

    // Edit Level Modal
    echo '<div class="gm-modal" id="gm-edit-level-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Edit Level</h2>
                <form method="post" action="' . admin_url('admin-post.php') . '">
                    <input type="hidden" name="action" value="edit_level">
                    <input type="hidden" id="gm-edit-level-id" name="level_id" value="">
                    <label for="gm-edit-level-name">Level Name</label>
                    <input type="text" id="gm-edit-level-name" name="level_name" required>
                    <br><input type="submit" value="Update Level">
                </form>
            </div>
          </div>';

    echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const addLevelTile = document.getElementById("gm-add-level-tile");
                const addLevelModal = document.getElementById("gm-add-level-modal");
                const editLevelModal = document.getElementById("gm-edit-level-modal");
                const closeModalBtns = document.querySelectorAll(".gm-modal-close");
                const editLevelBtns = document.querySelectorAll(".gm-edit-level-btn");
                const deleteLevelBtns = document.querySelectorAll(".gm-delete-level-btn");

                addLevelTile.addEventListener("click", function() {
                    addLevelModal.style.display = "flex";
                });

                closeModalBtns.forEach(btn => {
                    btn.addEventListener("click", function() {
                        addLevelModal.style.display = "none";
                        editLevelModal.style.display = "none";
                    });
                });

                window.addEventListener("click", function(event) {
                    if (event.target === addLevelModal || event.target === editLevelModal) {
                        addLevelModal.style.display = "none";
                        editLevelModal.style.display = "none";
                    }
                });

                editLevelBtns.forEach(btn => {
                    btn.addEventListener("click", function() {
                        const levelId = this.getAttribute("data-level-id");
                        const levelName = this.getAttribute("data-level-name");
                        document.getElementById("gm-edit-level-id").value = levelId;
                        document.getElementById("gm-edit-level-name").value = levelName;
                        editLevelModal.style.display = "flex";
                    });
                });

                deleteLevelBtns.forEach(btn => {
                    btn.addEventListener("click", function() {
                        const levelId = this.getAttribute("data-level-id");
                        if (confirm("Are you sure you want to delete this level?")) {
                            window.location.href = "' . admin_url('admin.php?page=gym-levels&delete_level=') . '" + levelId;
                        }
                    });
                });
            });
          </script>';
}

add_action('admin_post_add_level', 'wsm_levels_page');
add_action('admin_post_edit_level', 'wsm_levels_page');
?>
