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
        $athlete_allergies = isset($_POST['athlete_allergies']) ? 'yes' : 'no';
        $athlete_medical_info = sanitize_textarea_field($_POST['athlete_medical_info']);
        $parent_id = intval($_POST['parent_id']);

        $athletes = get_post_meta($parent_id, '_gm_parent_athletes', true);
        if (!$athletes) {
            $athletes = array();
        }
        $athlete_id = uniqid('athlete_', true);
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

    echo '<h1>Athlete Management</h1>';

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
            .gm-tiles-container .gm-dashboard-tile {
                flex: 1 1 calc(33.333% - 20px);
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
                max-width: 600px;
            }
            .gm-modal-close {
                float: right;
                cursor: pointer;
                font-size: 20px;
            }
            .gm-delete-athlete-btn {
                background-color: red;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                float: right;
                margin-left: 10px;
            }
            .gm-update-athlete-btn {
                background-color: #0073aa;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                float: left;
            }
            .gm-athlete-parent {
                font-style: italic;
                font-size: 0.9em;
                color: #555;
            }
            #gm-search-box {
                margin-bottom: 20px;
                width: 100%;
                max-width: 400px;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .gm-search-results {
                display: none;
                position: absolute;
                background-color: white;
                border: 1px solid #ccc;
                border-radius: 4px;
                max-width: 400px;
                z-index: 1000;
            }
            .gm-search-result-item {
                padding: 10px;
                cursor: pointer;
            }
            .gm-search-result-item:hover {
                background-color: #f1f1f1;
            }
            .form-group {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
            }
            .form-group label {
                flex: 1;
                margin-right: 10px;
            }
            .form-group input, .form-group select, .form-group textarea {
                flex: 2;
            }
            .form-group input[type="checkbox"] {
                flex: 0;
            }
          </style>';

    echo '<div style="display: flex; justify-content: space-between; align-items: center;">';
    echo '<input type="text" id="gm-search-box" placeholder="Search for athletes or parents...">';
    echo '<button id="gm-add-parent-btn" class="button button-primary">Add New Parent</button>';
    echo '</div>';
    echo '<div id="gm-search-results" class="gm-search-results"></div>';

    echo '<div class="gm-tiles-container">';
    foreach ($parents as $parent) {
        $athletes = get_post_meta($parent->ID, '_gm_parent_athletes', true);
        if ($athletes) {
            foreach ($athletes as $athlete_id => $athlete) {
                echo '<div class="gm-dashboard-tile gm-athlete-tile" data-athlete-id="' . esc_attr($athlete_id) . '" data-parent-id="' . esc_attr($parent->ID) . '">';
                echo esc_html($athlete['first_name']) . ' ' . esc_html($athlete['last_name']);
                echo '<div class="gm-athlete-parent">' . esc_html($parent->post_title) . '</div>';
                echo '</div>';
            }
        }
    }
    echo '<div class="gm-dashboard-tile" id="gm-add-athlete-tile">+</div>';
    echo '</div>';

    // Add Parent Modal
    echo '<div class="gm-modal" id="gm-add-parent-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Add New Parent</h2>
                <form id="gm-add-parent-form" method="post" action="">
                    <input type="hidden" name="action" value="add_parent">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" required>
                    <br><label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required>
                    <br><label for="address">Address</label>
                    <input type="text" name="address" id="address" required>
                    <br><label for="phone">Phone Number</label>
                    <input type="text" name="phone" id="phone" required>
                    <br><label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                    <br><input type="submit" value="Add Parent">
                </form>
            </div>
          </div>';

    // Add Athlete Modal
    echo '<div class="gm-modal" id="gm-add-athlete-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Add New Athlete to Parent</h2>
                <form id="gm-add-athlete-form" method="post" action="">
                    <input type="hidden" name="action" value="add_athlete">
                    <label for="parent_id">Select Parent</label>
                    <select name="parent_id" id="parent_id" required>';
    foreach ($parents as $parent) {
        echo '<option value="' . $parent->ID . '">' . esc_html($parent->post_title) . '</option>';
    }
    echo '      </select>
                    <br><label for="athlete_first_name">Athlete\'s First Name</label>
                    <input type="text" name="athlete_first_name" id="athlete_first_name" required>
                    <br><label for="athlete_last_name">Athlete\'s Last Name</label>
                    <input type="text" name="athlete_last_name" id="athlete_last_name" required>
                    <br><label for="athlete_gender">Gender</label>
                    <select name="athlete_gender" id="athlete_gender" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    <br><label for="athlete_dob">Date of Birth</label>
                    <input type="date" name="athlete_dob" id="athlete_dob" required>
                    <br><label for="athlete_allergies">Allergies</label>
                    <input type="checkbox" name="athlete_allergies" id="athlete_allergies">
                    <br><label for="athlete_medical_info" id="athlete_medical_info_label" style="display:none;">Additional Medical Info</label>
                    <textarea name="athlete_medical_info" id="athlete_medical_info" style="display:none;"></textarea>
                    <br><input type="submit" value="Add Athlete">
                </form>
            </div>
          </div>';

    // Edit Athlete Modal
    echo '<div class="gm-modal" id="gm-edit-athlete-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Edit Athlete</h2>
                <form id="gm-edit-athlete-form" method="post" action="">
                    <input type="hidden" name="action" value="gm_edit_athlete">
                    <input type="hidden" id="gm-edit-athlete-id" name="athlete_id" value="">
                    <input type="hidden" id="gm-original-parent-id" name="original_parent_id" value="">
                    <div class="form-group">
                        <label for="gm-edit-athlete-first-name">First Name</label>
                        <input type="text" id="gm-edit-athlete-first-name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="gm-edit-athlete-last-name">Last Name</label>
                        <input type="text" id="gm-edit-athlete-last-name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="gm-edit-athlete-dob">Date of Birth</label>
                        <input type="date" id="gm-edit-athlete-dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label for="gm-edit-athlete-gender">Gender</label>
                        <select id="gm-edit-athlete-gender" name="gender" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gm-edit-athlete-allergies">Allergies</label>
                        <input type="checkbox" id="gm-edit-athlete-allergies" name="allergies">
                    </div>
                    <div class="form-group" id="gm-medical-info-container" style="display:none;">
                        <label for="gm-edit-athlete-medical-info">Additional Medical Info</label>
                        <textarea id="gm-edit-athlete-medical-info" name="medical_info"></textarea>
                    </div>
                    <div class="form-group">
                        <select id="gm-edit-athlete-parent-id" name="parent_id">';
    foreach ($parents as $parent) {
        echo '<option value="' . $parent->ID . '">' . esc_html($parent->post_title) . '</option>';
    }
    echo '      </select>
                    </div>
                    <input type="submit" value="Update Athlete" class="gm-update-athlete-btn">
                    <button type="button" id="gm-delete-athlete-btn" class="gm-delete-athlete-btn">Delete Athlete</button>
                </form>
                <div style="clear:both;"></div>
                <h3>Assigned Classes</h3>
                <div id="gm-assigned-classes"></div>
                <select id="gm-assign-class-select">
                    <option value="">Assign Class</option>';
    $classes = get_posts(array(
        'post_type' => 'gm_class',
        'post_status' => 'publish',
        'numberposts' => -1,
    ));
    foreach ($classes as $class) {
        echo '<option value="' . $class->ID . '">' . esc_html($class->post_title) . '</option>';
    }
    echo '</select>
                <button id="gm-assign-class-btn" class="button button-primary">Assign Class</button>
            </div>
          </div>';

    echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const athleteTiles = document.querySelectorAll(".gm-athlete-tile");
                const addParentBtn = document.getElementById("gm-add-parent-btn");
                const addParentModal = document.getElementById("gm-add-parent-modal");
                const addAthleteTile = document.getElementById("gm-add-athlete-tile");
                const addAthleteModal = document.getElementById("gm-add-athlete-modal");
                const editAthleteModal = document.getElementById("gm-edit-athlete-modal");
                const closeModalBtns = document.querySelectorAll(".gm-modal-close");
                const deleteAthleteBtn = document.getElementById("gm-delete-athlete-btn");
                const searchBox = document.getElementById("gm-search-box");
                const searchResults = document.getElementById("gm-search-results");
                const allergiesCheckbox = document.getElementById("athlete_allergies");
                const medicalInfoTextarea = document.getElementById("athlete_medical_info");
                const medicalInfoLabel = document.getElementById("athlete_medical_info_label");

                allergiesCheckbox.addEventListener("change", function() {
                    if (this.checked) {
                        medicalInfoTextarea.style.display = "block";
                        medicalInfoLabel.style.display = "block";
                    } else {
                        medicalInfoTextarea.style.display = "none";
                        medicalInfoLabel.style.display = "none";
                    }
                });

                addParentBtn.addEventListener("click", function() {
                    addParentModal.style.display = "flex";
                });

                addAthleteTile.addEventListener("click", function() {
                    addAthleteModal.style.display = "flex";
                });

                closeModalBtns.forEach(btn => {
                    btn.addEventListener("click", function() {
                        addParentModal.style.display = "none";
                        addAthleteModal.style.display = "none";
                        editAthleteModal.style.display = "none";
                    });
                });

                window.addEventListener("click", function(event) {
                    if (event.target === addParentModal || event.target === addAthleteModal || event.target === editAthleteModal) {
                        addParentModal.style.display = "none";
                        addAthleteModal.style.display = "none";
                        editAthleteModal.style.display = "none";
                    }
                });

                athleteTiles.forEach(tile => {
                    tile.addEventListener("click", function() {
                        const athleteId = this.getAttribute("data-athlete-id");
                        const parentId = this.getAttribute("data-parent-id");
                        document.getElementById("gm-edit-athlete-id").value = athleteId;
                        document.getElementById("gm-original-parent-id").value = parentId;

                        fetch(ajaxurl, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                action: "gm_get_athlete_details",
                                athlete_id: athleteId,
                                parent_id: parentId
                            })
                        }).then(response => {
                            if (response.ok) {
                                return response.json();
                            }
                            throw new Error("Network response was not ok.");
                        })
                          .then(data => {
                              if (data.success) {
                                  document.querySelector("#gm-edit-athlete-modal h2").textContent = "Editing " + data.first_name + " " + data.last_name;
                                  document.getElementById("gm-edit-athlete-first-name").value = data.first_name;
                                  document.getElementById("gm-edit-athlete-last-name").value = data.last_name;
                                  document.getElementById("gm-edit-athlete-gender").value = data.gender;
                                  document.getElementById("gm-edit-athlete-dob").value = data.dob;
                                  document.getElementById("gm-edit-athlete-allergies").checked = data.allergies === "yes";
                                  document.getElementById("gm-edit-athlete-medical-info").value = data.medical_info;
                                  if (data.allergies === "yes") {
                                      document.getElementById("gm-medical-info-container").style.display = "flex";
                                  } else {
                                      document.getElementById("gm-medical-info-container").style.display = "none";
                                  }
                                  document.getElementById("gm-edit-athlete-parent-id").value = data.parent_id;

                                  const assignedClassesDiv = document.getElementById("gm-assigned-classes");
                                  assignedClassesDiv.innerHTML = "";
                                  if (data.assigned_classes.length > 0) {
                                      data.assigned_classes.forEach(classData => {
                                          const classDiv = document.createElement("div");
                                          classDiv.textContent = classData.name;
                                          const removeBtn = document.createElement("button");
                                          removeBtn.textContent = "X";
                                          removeBtn.className = "gm-delete-class-assignment-btn";
                                          removeBtn.setAttribute("data-class-id", classData.id);
                                          removeBtn.addEventListener("click", function() {
                                              fetch(ajaxurl, {
                                                  method: "POST",
                                                  headers: {
                                                      "Content-Type": "application/x-www-form-urlencoded"
                                                  },
                                                  body: new URLSearchParams({
                                                      action: "gm_remove_athlete_from_class",
                                                      class_id: classData.id,
                                                      athlete_id: athleteId
                                                  })
                                              }).then(response => {
                                                  if (response.ok) {
                                                      return response.json();
                                                  }
                                                  throw new Error("Network response was not ok.");
                                              })
                                                .then(response => {
                                                    if (response.success) {
                                                        assignedClassesDiv.removeChild(classDiv);
                                                    } else {
                                                        console.error("Failed to remove class assignment");
                                                    }
                                                })
                                                .catch(error => console.error("Fetch error:", error));
                                          });
                                          classDiv.appendChild(removeBtn);
                                          assignedClassesDiv.appendChild(classDiv);
                                      });
                                  } else {
                                      assignedClassesDiv.textContent = "No assigned classes.";
                                  }

                                  editAthleteModal.style.display = "flex";
                              } else {
                                  console.error("Failed to load athlete details");
                              }
                          }).catch(error => console.error("Fetch error:", error));
                    });
                });

                deleteAthleteBtn.addEventListener("click", function() {
                    const athleteId = document.getElementById("gm-edit-athlete-id").value;
                    const parentId = document.getElementById("gm-original-parent-id").value;
                    if (confirm("Are you sure you want to delete this athlete?")) {
                        fetch(ajaxurl, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                action: "gm_delete_athlete",
                                athlete_id: athleteId,
                                parent_id: parentId
                            })
                        }).then(response => {
                            if (response.ok) {
                                return response.json();
                            }
                            throw new Error("Network response was not ok.");
                        })
                          .then(data => {
                              if (data.success) {
                                  location.reload();
                              } else {
                                  console.error("Failed to delete athlete");
                              }
                          }).catch(error => console.error("Fetch error:", error));
                    }
                });

                document.getElementById("gm-edit-athlete-allergies").addEventListener("change", function() {
                    if (this.checked) {
                        document.getElementById("gm-medical-info-container").style.display = "flex";
                    } else {
                        document.getElementById("gm-medical-info-container").style.display = "none";
                    }
                });

                document.getElementById("gm-edit-athlete-form").addEventListener("submit", function(event) {
                    event.preventDefault();
                    const formData = new FormData(this);
                    fetch(ajaxurl, {
                        method: "POST",
                        body: formData
                    }).then(response => {
                        if (response.ok) {
                            return response.json();
                        }
                        throw new Error("Network response was not ok.");
                    })
                      .then(data => {
                          if (data.success) {
                              location.reload();
                          } else {
                              console.error("Failed to update athlete");
                          }
                      }).catch(error => console.error("Fetch error:", error));
                });

                document.getElementById("gm-assign-class-btn").addEventListener("click", function() {
                    const classId = document.getElementById("gm-assign-class-select").value;
                    const athleteId = document.getElementById("gm-edit-athlete-id").value;
                    if (classId) {
                        fetch(ajaxurl, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                action: "gm_assign_class_to_athlete",
                                class_id: classId,
                                athlete_id: athleteId
                            })
                        }).then(response => {
                            if (response.ok) {
                                return response.json();
                            }
                            throw new Error("Network response was not ok.");
                        })
                          .then(data => {
                              if (data.success) {
                                  location.reload();
                              } else {
                                  console.error("Failed to assign class");
                              }
                          }).catch(error => console.error("Fetch error:", error));
                    }
                });

                searchBox.addEventListener("input", function() {
                    const query = this.value.toLowerCase();
                    if (query.length < 2) {
                        searchResults.style.display = "none";
                        return;
                    }

                    fetch(ajaxurl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: new URLSearchParams({
                            action: "gm_search_athletes_parents",
                            query: query
                        })
                    }).then(response => {
                        if (response.ok) {
                            return response.json();
                        }
                        throw new Error("Network response was not ok.");
                    })
                      .then(data => {
                          if (data.success) {
                              searchResults.innerHTML = "";
                              data.results.forEach(result => {
                                  const item = document.createElement("div");
                                  item.className = "gm-search-result-item";
                                  item.textContent = result.name;
                                  item.addEventListener("click", function() {
                                      const parentId = result.parent_id;
                                      const athleteId = result.athlete_id;
                                      if (athleteId) {
                                          fetch(ajaxurl, {
                                              method: "POST",
                                              headers: {
                                                  "Content-Type": "application/x-www-form-urlencoded"
                                              },
                                              body: new URLSearchParams({
                                                  action: "gm_get_athlete_details",
                                                  athlete_id: athleteId,
                                                  parent_id: parentId
                                              })
                                          }).then(response => {
                                              if (response.ok) {
                                                  return response.json();
                                              }
                                              throw new Error("Network response was not ok.");
                                          })
                                            .then(data => {
                                                if (data.success) {
                                                    document.getElementById("gm-edit-athlete-id").value = athleteId;
                                                    document.getElementById("gm-original-parent-id").value = parentId;
                                                    document.getElementById("gm-edit-athlete-first-name").value = data.first_name;
                                                    document.getElementById("gm-edit-athlete-last-name").value = data.last_name;
                                                    document.getElementById("gm-edit-athlete-gender").value = data.gender;
                                                    document.getElementById("gm-edit-athlete-dob").value = data.dob;
                                                    document.getElementById("gm-edit-athlete-allergies").checked = data.allergies === "yes";
                                                    document.getElementById("gm-edit-athlete-medical-info").value = data.medical_info;
                                                    if (data.allergies === "yes") {
                                                        document.getElementById("gm-medical-info-container").style.display = "flex";
                                                    } else {
                                                        document.getElementById("gm-medical-info-container").style.display = "none";
                                                    }
                                                    document.getElementById("gm-edit-athlete-parent-id").value = data.parent_id;

                                                    const assignedClassesDiv = document.getElementById("gm-assigned-classes");
                                                    assignedClassesDiv.innerHTML = "";
                                                    if (data.assigned_classes.length > 0) {
                                                        data.assigned_classes.forEach(classData => {
                                                            const classDiv = document.createElement("div");
                                                            classDiv.textContent = classData.name;
                                                            const removeBtn = document.createElement("button");
                                                            removeBtn.textContent = "X";
                                                            removeBtn.className = "gm-delete-class-assignment-btn";
                                                            removeBtn.setAttribute("data-class-id", classData.id);
                                                            removeBtn.addEventListener("click", function() {
                                                                fetch(ajaxurl, {
                                                                    method: "POST",
                                                                    headers: {
                                                                        "Content-Type": "application/x-www-form-urlencoded"
                                                                    },
                                                                    body: new URLSearchParams({
                                                                        action: "gm_remove_athlete_from_class",
                                                                        class_id: classData.id,
                                                                        athlete_id: athleteId
                                                                    })
                                                                }).then(response => {
                                                                    if (response.ok) {
                                                                        return response.json();
                                                                    }
                                                                    throw new Error("Network response was not ok.");
                                                                })
                                                                  .then(response => {
                                                                      if (response.success) {
                                                                          assignedClassesDiv.removeChild(classDiv);
                                                                      } else {
                                                                          console.error("Failed to remove class assignment");
                                                                      }
                                                                  })
                                                                  .catch(error => console.error("Fetch error:", error));
                                                            });
                                                            classDiv.appendChild(removeBtn);
                                                            assignedClassesDiv.appendChild(classDiv);
                                                        });
                                                    } else {
                                                        assignedClassesDiv.textContent = "No assigned classes.";
                                                    }

                                                    editAthleteModal.style.display = "flex";
                                                } else {
                                                    console.error("Failed to load athlete details");
                                                }
                                            }).catch(error => console.error("Fetch error:", error));
                                      } else {
                                          window.location.href = result.url;
                                      }
                                  });
                                  searchResults.appendChild(item);
                              });
                              searchResults.style.display = "block";
                          } else {
                              searchResults.style.display = "none";
                          }
                      }).catch(error => console.error("Fetch error:", error));
                });
            });
          </script>';
}

// Handle editing an athlete
if (!function_exists('gm_edit_athlete')) {
    function gm_edit_athlete() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
        }

        if (isset($_POST['athlete_id']) && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['gender']) && isset($_POST['dob'])) {
            $athlete_id = sanitize_text_field($_POST['athlete_id']);
            $original_parent_id = intval($_POST['original_parent_id']);
            $new_parent_id = intval($_POST['parent_id']);
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $gender = sanitize_text_field($_POST['gender']);
            $dob = sanitize_text_field($_POST['dob']);
            $allergies = isset($_POST['allergies']) ? 'yes' : 'no';
            $medical_info = sanitize_textarea_field($_POST['medical_info']);

            // Remove athlete from original parent's list
            $original_athletes = get_post_meta($original_parent_id, '_gm_parent_athletes', true);
            if (isset($original_athletes[$athlete_id])) {
                unset($original_athletes[$athlete_id]);
                update_post_meta($original_parent_id, '_gm_parent_athletes', $original_athletes);
            }

            // Add athlete to new parent's list
            $new_athletes = get_post_meta($new_parent_id, '_gm_parent_athletes', true);
            if (!$new_athletes) {
                $new_athletes = array();
            }
            $new_athletes[$athlete_id] = array(
                'id' => $athlete_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'gender' => $gender,
                'dob' => $dob,
                'allergies' => $allergies,
                'medical_info' => $medical_info,
            );
            update_post_meta($new_parent_id, '_gm_parent_athletes', $new_athletes);

            // Update class assignments
            $assigned_classes = get_posts(array(
                'post_type' => 'gm_class',
                'post_status' => 'publish',
                'numberposts' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_gm_class_athletes',
                        'value' => '"' . $athlete_id . '"',
                        'compare' => 'LIKE',
                    ),
                ),
            ));
            foreach ($assigned_classes as $class) {
                $class_athletes = get_post_meta($class->ID, '_gm_class_athletes', true);
                if (is_array($class_athletes)) {
                    if (($key = array_search($athlete_id, $class_athletes)) !== false) {
                        $class_athletes[$key] = $athlete_id;
                        update_post_meta($class->ID, '_gm_class_athletes', $class_athletes);
                    }
                }
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }

        wp_die();
    }
}
add_action('wp_ajax_gm_edit_athlete', 'gm_edit_athlete');

// Handle deleting an athlete
if (!function_exists('gm_delete_athlete')) {
    function gm_delete_athlete() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
        }

        if (isset($_POST['athlete_id']) && isset($_POST['parent_id'])) {
            $athlete_id = sanitize_text_field($_POST['athlete_id']);
            $parent_id = intval($_POST['parent_id']);

            $athletes = get_post_meta($parent_id, '_gm_parent_athletes', true);
            if (isset($athletes[$athlete_id])) {
                unset($athletes[$athlete_id]);
                update_post_meta($parent_id, '_gm_parent_athletes', $athletes);

                // Remove athlete from all classes
                $classes = get_posts(array(
                    'post_type' => 'gm_class',
                    'post_status' => 'publish',
                    'numberposts' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_gm_class_athletes',
                            'value' => '"' . $athlete_id . '"',
                            'compare' => 'LIKE',
                        ),
                    ),
                ));
                foreach ($classes as $class) {
                    $class_athletes = get_post_meta($class->ID, '_gm_class_athletes', true);
                    if (is_array($class_athletes)) {
                        if (($key = array_search($athlete_id, $class_athletes)) !== false) {
                            unset($class_athletes[$key]);
                            update_post_meta($class->ID, '_gm_class_athletes', array_values($class_athletes));
                        }
                    }
                }

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
        } else {
            echo json_encode(['success' => false]);
        }

        wp_die();
    }
}
add_action('wp_ajax_gm_delete_athlete', 'gm_delete_athlete');

// Handle getting athlete details for editing
if (!function_exists('gm_get_athlete_details')) {
    function gm_get_athlete_details() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
        }

        if (isset($_POST['athlete_id']) && isset($_POST['parent_id'])) {
            $athlete_id = sanitize_text_field($_POST['athlete_id']);
            $parent_id = intval($_POST['parent_id']);

            $athletes = get_post_meta($parent_id, '_gm_parent_athletes', true);
            if (isset($athletes[$athlete_id])) {
                $athlete = $athletes[$athlete_id];

                // Fetch assigned classes
                $assigned_classes = get_posts(array(
                    'post_type' => 'gm_class',
                    'post_status' => 'publish',
                    'numberposts' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_gm_class_athletes',
                            'value' => '"' . $athlete_id . '"',
                            'compare' => 'LIKE',
                        ),
                    ),
                ));
                $classes_data = array();
                foreach ($assigned_classes as $class) {
                    $classes_data[] = array('id' => $class->ID, 'name' => $class->post_title);
                }

                echo json_encode([
                    'success' => true,
                    'first_name' => $athlete['first_name'],
                    'last_name' => $athlete['last_name'],
                    'gender' => $athlete['gender'],
                    'dob' => $athlete['dob'],
                    'allergies' => $athlete['allergies'],
                    'medical_info' => $athlete['medical_info'],
                    'parent_id' => $parent_id,
                    'assigned_classes' => $classes_data,
                ]);
            } else {
                echo json_encode(['success' => false]);
            }
        } else {
            echo json_encode(['success' => false]);
        }

        wp_die();
    }
}
add_action('wp_ajax_gm_get_athlete_details', 'gm_get_athlete_details');

// Handle searching athletes and parents
if (!function_exists('gm_search_athletes_parents')) {
    function gm_search_athletes_parents() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
        }

        if (isset($_POST['query'])) {
            $query = sanitize_text_field($_POST['query']);
            $results = [];

            // Search parents
            $parent_posts = get_posts(array(
                'post_type' => 'gm_parent',
                'post_status' => 'publish',
                's' => $query,
                'numberposts' => -1,
            ));
            foreach ($parent_posts as $parent) {
                $results[] = [
                    'name' => $parent->post_title,
                    'url' => admin_url('admin.php?page=gym-parents&parent_id=' . $parent->ID),
                    'parent_id' => $parent->ID,
                    'athlete_id' => null,
                ];
            }

            // Search athletes
            $parent_posts = get_posts(array(
                'post_type' => 'gm_parent',
                'post_status' => 'publish',
                'numberposts' => -1,
            ));
            foreach ($parent_posts as $parent) {
                $athletes = get_post_meta($parent->ID, '_gm_parent_athletes', true);
                if ($athletes) {
                    foreach ($athletes as $athlete) {
                        if (stripos($athlete['first_name'], $query) !== false || stripos($athlete['last_name'], $query) !== false) {
                            $results[] = [
                                'name' => $athlete['first_name'] . ' ' . $athlete['last_name'] . ' (Parent: ' . $parent->post_title . ')',
                                'url' => admin_url('admin.php?page=gym-parents&athlete_id=' . $athlete['id'] . '&parent_id=' . $parent->ID),
                                'parent_id' => $parent->ID,
                                'athlete_id' => $athlete['id'],
                            ];
                        }
                    }
                }
            }

            echo json_encode(['success' => true, 'results' => $results]);
        } else {
            echo json_encode(['success' => false]);
        }

        wp_die();
    }
}
add_action('wp_ajax_gm_search_athletes_parents', 'gm_search_athletes_parents');

// Handle assigning a class to an athlete
if (!function_exists('gm_assign_class_to_athlete')) {
    function gm_assign_class_to_athlete() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
        }

        if (isset($_POST['class_id']) && isset($_POST['athlete_id'])) {
            $class_id = intval($_POST['class_id']);
            $athlete_id = sanitize_text_field($_POST['athlete_id']);
            $assigned_athletes = get_post_meta($class_id, '_gm_class_athletes', true);
            if (!is_array($assigned_athletes)) {
                $assigned_athletes = array();
            }

            if (!in_array($athlete_id, $assigned_athletes)) {
                $assigned_athletes[] = $athlete_id;
                update_post_meta($class_id, '_gm_class_athletes', $assigned_athletes);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
        } else {
            echo json_encode(['success' => false]);
        }

        wp_die();
    }
}
add_action('wp_ajax_gm_assign_class_to_athlete', 'gm_assign_class_to_athlete');

// Handle removing an athlete from a class
if (!function_exists('gm_remove_athlete_from_class')) {
    function gm_remove_athlete_from_class() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
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
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
        } else {
            echo json_encode(['success' => false]);
        }

        wp_die();
    }
}
add_action('wp_ajax_gm_remove_athlete_from_class', 'gm_remove_athlete_from_class');
?>
