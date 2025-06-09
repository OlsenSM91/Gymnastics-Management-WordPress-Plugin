<?php
function wsm_families_page() {
    global $wpdb;

    // Handle adding a new family
    if (isset($_POST['action']) && $_POST['action'] === 'add_family') {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $address = sanitize_text_field($_POST['address']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);

        $family_id = wp_insert_post(array(
            'post_title' => $first_name . ' ' . $last_name,
            'post_type' => 'wsm_family',
            'post_status' => 'publish',
            'meta_input' => array(
                '_wsm_family_first_name' => $first_name,
                '_wsm_family_last_name' => $last_name,
                '_wsm_family_address' => $address,
                '_wsm_family_phone' => $phone,
                '_wsm_family_email' => $email,
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
        $family_id = intval($_POST['family_id']);

        $athletes = get_post_meta($family_id, '_wsm_family_athletes', true);
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
        update_post_meta($family_id, '_wsm_family_athletes', $athletes);
    }

    // Handle deleting a family
    if (isset($_GET['delete_family'])) {
        $family_id = intval($_GET['delete_family']);
        wp_delete_post($family_id);
        echo '<meta http-equiv="refresh" content="0; url=?page=gym-familys">';
    }

    // Fetch all familys
    $familys = get_posts(array(
        'post_type' => 'wsm_family',
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
            .gm-athlete-family {
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
    echo '<input type="text" id="gm-search-box" placeholder="Search for athletes or familys...">';
    echo '<button id="gm-add-family-btn" class="button button-primary">Add New Parent</button>';
    echo '</div>';
    echo '<div id="gm-search-results" class="gm-search-results"></div>';

    echo '<div class="gm-tiles-container">';
    foreach ($familys as $family) {
        $athletes = get_post_meta($family->ID, '_wsm_family_athletes', true);
        if ($athletes) {
            foreach ($athletes as $athlete_id => $athlete) {
                echo '<div class="gm-dashboard-tile gm-athlete-tile" data-athlete-id="' . esc_attr($athlete_id) . '" data-family-id="' . esc_attr($family->ID) . '">';
                echo esc_html($athlete['first_name']) . ' ' . esc_html($athlete['last_name']);
                echo '<div class="gm-athlete-family">' . esc_html($family->post_title) . '</div>';
                echo '</div>';
            }
        }
    }
    echo '<div class="gm-dashboard-tile" id="gm-add-athlete-tile">+</div>';
    echo '</div>';

    // Add Parent Modal
    echo '<div class="gm-modal" id="gm-add-family-modal">
            <div class="gm-modal-content">
                <span class="gm-modal-close">&times;</span>
                <h2>Add New Parent</h2>
                <form id="gm-add-family-form" method="post" action="">
                    <input type="hidden" name="action" value="add_family">
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
                    <label for="family_id">Select Parent</label>
                    <select name="family_id" id="family_id" required>';
    foreach ($familys as $family) {
        echo '<option value="' . $family->ID . '">' . esc_html($family->post_title) . '</option>';
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
                    <input type="hidden" name="action" value="wsm_edit_athlete">
                    <input type="hidden" id="gm-edit-athlete-id" name="athlete_id" value="">
                    <input type="hidden" id="gm-original-family-id" name="original_family_id" value="">
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
                        <select id="gm-edit-athlete-family-id" name="family_id">';
    foreach ($familys as $family) {
        echo '<option value="' . $family->ID . '">' . esc_html($family->post_title) . '</option>';
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
        'post_type' => 'wsm_class',
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
                const addParentBtn = document.getElementById("gm-add-family-btn");
                const addParentModal = document.getElementById("gm-add-family-modal");
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
                        const familyId = this.getAttribute("data-family-id");
                        document.getElementById("gm-edit-athlete-id").value = athleteId;
                        document.getElementById("gm-original-family-id").value = familyId;

                        fetch(ajaxurl, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                action: "wsm_get_athlete_details",
                                athlete_id: athleteId,
                                family_id: familyId
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
                                  document.getElementById("gm-edit-athlete-family-id").value = data.family_id;

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
                                                      action: "wsm_remove_athlete_from_session",
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
                    const familyId = document.getElementById("gm-original-family-id").value;
                    if (confirm("Are you sure you want to delete this athlete?")) {
                        fetch(ajaxurl, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                action: "wsm_delete_athlete",
                                athlete_id: athleteId,
                                family_id: familyId
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
                                action: "wsm_assign_session_to_athlete",
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
                            action: "wsm_search_athletes_familys",
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
                                      const familyId = result.family_id;
                                      const athleteId = result.athlete_id;
                                      if (athleteId) {
                                          fetch(ajaxurl, {
                                              method: "POST",
                                              headers: {
                                                  "Content-Type": "application/x-www-form-urlencoded"
                                              },
                                              body: new URLSearchParams({
                                                  action: "wsm_get_athlete_details",
                                                  athlete_id: athleteId,
                                                  family_id: familyId
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
                                                    document.getElementById("gm-original-family-id").value = familyId;
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
                                                    document.getElementById("gm-edit-athlete-family-id").value = data.family_id;

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
                                                                        action: "wsm_remove_athlete_from_session",
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
if (!function_exists('wsm_edit_athlete')) {
    function wsm_edit_athlete() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
        }

        if (isset($_POST['athlete_id']) && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['gender']) && isset($_POST['dob'])) {
            $athlete_id = sanitize_text_field($_POST['athlete_id']);
            $original_family_id = intval($_POST['original_family_id']);
            $new_family_id = intval($_POST['family_id']);
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $gender = sanitize_text_field($_POST['gender']);
            $dob = sanitize_text_field($_POST['dob']);
            $allergies = isset($_POST['allergies']) ? 'yes' : 'no';
            $medical_info = sanitize_textarea_field($_POST['medical_info']);

            // Remove athlete from original family's list
            $original_athletes = get_post_meta($original_family_id, '_wsm_family_athletes', true);
            if (isset($original_athletes[$athlete_id])) {
                unset($original_athletes[$athlete_id]);
                update_post_meta($original_family_id, '_wsm_family_athletes', $original_athletes);
            }

            // Add athlete to new family's list
            $new_athletes = get_post_meta($new_family_id, '_wsm_family_athletes', true);
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
            update_post_meta($new_family_id, '_wsm_family_athletes', $new_athletes);

            // Update class assignments
            $assigned_classes = get_posts(array(
                'post_type' => 'wsm_class',
                'post_status' => 'publish',
                'numberposts' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_wsm_session_athletes',
                        'value' => '"' . $athlete_id . '"',
                        'compare' => 'LIKE',
                    ),
                ),
            ));
            foreach ($assigned_classes as $class) {
                $class_athletes = get_post_meta($class->ID, '_wsm_session_athletes', true);
                if (is_array($class_athletes)) {
                    if (($key = array_search($athlete_id, $class_athletes)) !== false) {
                        $class_athletes[$key] = $athlete_id;
                        update_post_meta($class->ID, '_wsm_session_athletes', $class_athletes);
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
add_action('wp_ajax_wsm_edit_athlete', 'wsm_edit_athlete');

// Handle deleting an athlete
if (!function_exists('wsm_delete_athlete')) {
    function wsm_delete_athlete() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
        }

        if (isset($_POST['athlete_id']) && isset($_POST['family_id'])) {
            $athlete_id = sanitize_text_field($_POST['athlete_id']);
            $family_id = intval($_POST['family_id']);

            $athletes = get_post_meta($family_id, '_wsm_family_athletes', true);
            if (isset($athletes[$athlete_id])) {
                unset($athletes[$athlete_id]);
                update_post_meta($family_id, '_wsm_family_athletes', $athletes);

                // Remove athlete from all classes
                $classes = get_posts(array(
                    'post_type' => 'wsm_class',
                    'post_status' => 'publish',
                    'numberposts' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_wsm_session_athletes',
                            'value' => '"' . $athlete_id . '"',
                            'compare' => 'LIKE',
                        ),
                    ),
                ));
                foreach ($classes as $class) {
                    $class_athletes = get_post_meta($class->ID, '_wsm_session_athletes', true);
                    if (is_array($class_athletes)) {
                        if (($key = array_search($athlete_id, $class_athletes)) !== false) {
                            unset($class_athletes[$key]);
                            update_post_meta($class->ID, '_wsm_session_athletes', array_values($class_athletes));
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
add_action('wp_ajax_wsm_delete_athlete', 'wsm_delete_athlete');

// Handle getting athlete details for editing
if (!function_exists('wsm_get_athlete_details')) {
    function wsm_get_athlete_details() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
        }

        if (isset($_POST['athlete_id']) && isset($_POST['family_id'])) {
            $athlete_id = sanitize_text_field($_POST['athlete_id']);
            $family_id = intval($_POST['family_id']);

            $athletes = get_post_meta($family_id, '_wsm_family_athletes', true);
            if (isset($athletes[$athlete_id])) {
                $athlete = $athletes[$athlete_id];

                // Fetch assigned classes
                $assigned_classes = get_posts(array(
                    'post_type' => 'wsm_class',
                    'post_status' => 'publish',
                    'numberposts' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_wsm_session_athletes',
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
                    'family_id' => $family_id,
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
add_action('wp_ajax_wsm_get_athlete_details', 'wsm_get_athlete_details');

// Handle searching athletes and familys
if (!function_exists('wsm_search_athletes_familys')) {
    function wsm_search_athletes_familys() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
        }

        if (isset($_POST['query'])) {
            $query = sanitize_text_field($_POST['query']);
            $results = [];

            // Search familys
            $family_posts = get_posts(array(
                'post_type' => 'wsm_family',
                'post_status' => 'publish',
                's' => $query,
                'numberposts' => -1,
            ));
            foreach ($family_posts as $family) {
                $results[] = [
                    'name' => $family->post_title,
                    'url' => admin_url('admin.php?page=gym-familys&family_id=' . $family->ID),
                    'family_id' => $family->ID,
                    'athlete_id' => null,
                ];
            }

            // Search athletes
            $family_posts = get_posts(array(
                'post_type' => 'wsm_family',
                'post_status' => 'publish',
                'numberposts' => -1,
            ));
            foreach ($family_posts as $family) {
                $athletes = get_post_meta($family->ID, '_wsm_family_athletes', true);
                if ($athletes) {
                    foreach ($athletes as $athlete) {
                        if (stripos($athlete['first_name'], $query) !== false || stripos($athlete['last_name'], $query) !== false) {
                            $results[] = [
                                'name' => $athlete['first_name'] . ' ' . $athlete['last_name'] . ' (Parent: ' . $family->post_title . ')',
                                'url' => admin_url('admin.php?page=gym-familys&athlete_id=' . $athlete['id'] . '&family_id=' . $family->ID),
                                'family_id' => $family->ID,
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
add_action('wp_ajax_wsm_search_athletes_familys', 'wsm_search_athletes_familys');

// Handle assigning a class to an athlete
if (!function_exists('wsm_assign_session_to_athlete')) {
    function wsm_assign_session_to_athlete() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
        }

        if (isset($_POST['class_id']) && isset($_POST['athlete_id'])) {
            $class_id = intval($_POST['class_id']);
            $athlete_id = sanitize_text_field($_POST['athlete_id']);
            $assigned_athletes = get_post_meta($class_id, '_wsm_session_athletes', true);
            if (!is_array($assigned_athletes)) {
                $assigned_athletes = array();
            }

            if (!in_array($athlete_id, $assigned_athletes)) {
                $assigned_athletes[] = $athlete_id;
                update_post_meta($class_id, '_wsm_session_athletes', $assigned_athletes);
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
add_action('wp_ajax_wsm_assign_session_to_athlete', 'wsm_assign_session_to_athlete');

// Handle removing an athlete from a class
if (!function_exists('wsm_remove_athlete_from_session')) {
    function wsm_remove_athlete_from_session() {
        if (!current_user_can('manage_options')) {
            echo json_encode(['success' => false]);
            wp_die();
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
add_action('wp_ajax_wsm_remove_athlete_from_session', 'wsm_remove_athlete_from_session');
?>
