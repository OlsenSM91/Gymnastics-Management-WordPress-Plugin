// WP Studio Manager - Classes admin interactions
(function(){
    document.addEventListener('DOMContentLoaded', function(){
        const adminPost = wsmClasses.adminPostUrl;
        const adminAjax = wsmClasses.adminAjaxUrl;
        const deleteNonce = wsmClasses.deleteNonce;

        const assignBtns = document.querySelectorAll('.gm-assign-athlete-btn');
        const assignCoachBtns = document.querySelectorAll('.gm-assign-coach-btn');
        const assignModal = document.getElementById('gm-assign-modal');
        const assignCoachModal = document.getElementById('gm-assign-coach-modal');
        const editModal = document.getElementById('gm-edit-modal');
        const addModal = document.getElementById('gm-add-modal');
        const closeModalBtns = document.querySelectorAll('.gm-modal-close');
        const addClassBtn = document.getElementById('gm-add-class-btn');

        assignBtns.forEach(btn => {
            btn.addEventListener('click', function(){
                const classId = this.getAttribute('data-class-id');
                document.getElementById('gm-modal-class-id').value = classId;
                assignModal.style.display = 'flex';
            });
        });

        assignCoachBtns.forEach(btn => {
            btn.addEventListener('click', function(){
                const classId = this.getAttribute('data-class-id');
                document.getElementById('gm-modal-class-id-coach').value = classId;
                assignCoachModal.style.display = 'flex';
            });
        });

        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', function(){
                assignModal.style.display = 'none';
                assignCoachModal.style.display = 'none';
                editModal.style.display = 'none';
                addModal.style.display = 'none';
            });
        });

        window.addEventListener('click', function(event){
            if (event.target === assignModal || event.target === assignCoachModal || event.target === editModal || event.target === addModal) {
                assignModal.style.display = 'none';
                assignCoachModal.style.display = 'none';
                editModal.style.display = 'none';
                addModal.style.display = 'none';
            }
        });

        if(addClassBtn){
            addClassBtn.addEventListener('click', function(){
                addModal.style.display = 'flex';
            });
        }

        document.querySelectorAll('.gm-remove-athlete').forEach(btn => {
            btn.addEventListener('click', function(){
                const athleteId = this.getAttribute('data-athlete-id');
                const classId = this.getAttribute('data-class-id');
                fetch(adminPost, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=gm_remove_athlete&class_id=' + classId + '&athlete_id=' + athleteId
                }).then(r => r.text()).then(data => {
                    if(data === 'success'){ window.location.href = wsmClasses.currentPage; } else { console.error('Failed to remove athlete'); }
                });
            });
        });

        document.querySelectorAll('.gm-remove-coach').forEach(btn => {
            btn.addEventListener('click', function(){
                const coachId = this.getAttribute('data-coach-id');
                const classId = this.getAttribute('data-class-id');
                fetch(adminPost, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=gm_remove_coach&class_id=' + classId + '&coach_id=' + coachId
                }).then(r => r.text()).then(data => {
                    if(data === 'success'){ window.location.href = wsmClasses.currentPage; } else { console.error('Failed to remove coach'); }
                });
            });
        });

        document.querySelectorAll('.gm-edit-class-btn').forEach(btn => {
            btn.addEventListener('click', function(){
                const classId = this.getAttribute('data-class-id');
                document.getElementById('gm-edit-class-id').value = classId;
                fetch(adminAjax, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=gm_get_class_details&class_id=' + classId
                }).then(r => r.json()).then(data => {
                    if(data.success){
                        document.getElementById('gm-edit-class-name').value = data.class_name;
                        document.getElementById('gm-edit-available-seats').value = data.available_slots;
                        document.getElementById('gm-edit-class-price').value = data.class_price;
                        const scheduleContainer = document.getElementById('gm-edit-schedule-container');
                        scheduleContainer.innerHTML = '';
                        data.class_schedule.forEach((schedule_item, index) => {
                            const row = document.createElement('div');
                            row.className = 'schedule-row';
                            row.innerHTML = `
                                <select name="class_schedule[${index}][day]" required>
                                    <option value="monday" ${schedule_item.day === 'monday' ? 'selected' : ''}>Monday</option>
                                    <option value="tuesday" ${schedule_item.day === 'tuesday' ? 'selected' : ''}>Tuesday</option>
                                    <option value="wednesday" ${schedule_item.day === 'wednesday' ? 'selected' : ''}>Wednesday</option>
                                    <option value="thursday" ${schedule_item.day === 'thursday' ? 'selected' : ''}>Thursday</option>
                                    <option value="friday" ${schedule_item.day === 'friday' ? 'selected' : ''}>Friday</option>
                                    <option value="saturday" ${schedule_item.day === 'saturday' ? 'selected' : ''}>Saturday</option>
                                    <option value="sunday" ${schedule_item.day === 'sunday' ? 'selected' : ''}>Sunday</option>
                                </select>
                                <input type="time" name="class_schedule[${index}][start_time]" value="${schedule_item.start_time}" required>
                                <input type="time" name="class_schedule[${index}][end_time]" value="${schedule_item.end_time}" required>
                                <select name="class_schedule[${index}][frequency]" required>
                                    <option value="daily" ${schedule_item.frequency === 'daily' ? 'selected' : ''}>Daily</option>
                                    <option value="weekly" ${schedule_item.frequency === 'weekly' ? 'selected' : ''}>Weekly</option>
                                    <option value="monthly" ${schedule_item.frequency === 'monthly' ? 'selected' : ''}>Monthly</option>
                                </select>
                                <button type="button" class="remove-schedule">-</button>
                            `;
                            scheduleContainer.appendChild(row);
                        });
                        editModal.style.display = 'flex';
                    }else{
                        console.error('Failed to load class details');
                    }
                });
            });
        });

        document.getElementById('gm-add-schedule').addEventListener('click', function(){
            const container = document.getElementById('gm-add-schedule-container');
            const index = container.children.length;
            const row = document.createElement('div');
            row.className = 'schedule-row';
            row.innerHTML = `
                <select name="class_schedule[${index}][day]" required>
                    <option value="monday">Monday</option>
                    <option value="tuesday">Tuesday</option>
                    <option value="wednesday">Wednesday</option>
                    <option value="thursday">Thursday</option>
                    <option value="friday">Friday</option>
                    <option value="saturday">Saturday</option>
                    <option value="sunday">Sunday</option>
                </select>
                <input type="time" name="class_schedule[${index}][start_time]" required>
                <input type="time" name="class_schedule[${index}][end_time]" required>
                <select name="class_schedule[${index}][frequency]" required>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
                <button type="button" class="remove-schedule">-</button>`;
            container.appendChild(row);
        });

        document.getElementById('gm-add-edit-schedule').addEventListener('click', function(){
            const container = document.getElementById('gm-edit-schedule-container');
            const index = container.children.length;
            const row = document.createElement('div');
            row.className = 'schedule-row';
            row.innerHTML = `
                <select name="class_schedule[${index}][day]" required>
                    <option value="monday">Monday</option>
                    <option value="tuesday">Tuesday</option>
                    <option value="wednesday">Wednesday</option>
                    <option value="thursday">Thursday</option>
                    <option value="friday">Friday</option>
                    <option value="saturday">Saturday</option>
                    <option value="sunday">Sunday</option>
                </select>
                <input type="time" name="class_schedule[${index}][start_time]" required>
                <input type="time" name="class_schedule[${index}][end_time]" required>
                <select name="class_schedule[${index}][frequency]" required>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
                <button type="button" class="remove-schedule">-</button>`;
            container.appendChild(row);
        });

        document.getElementById('gm-delete-class-btn').addEventListener('click', function(){
            const classId = document.getElementById('gm-edit-class-id').value;
            if(confirm('Are you sure you want to delete this class?')){
                fetch(adminPost, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=gm_delete_class&class_id=' + classId + '&_wpnonce=' + deleteNonce
                }).then(r => r.text()).then(data => {
                    if(data === 'success'){ window.location.href = wsmClasses.currentPage; } else { console.error('Failed to delete class'); }
                });
            }
        });
    });
})();
