jQuery(document).ready(function($) {
    let scheduleIndex = $('.schedule-row').length;

    $('#add-schedule').click(function() {
        const newRow = `
            <div class="schedule-row">
                <select name="class_schedule[${scheduleIndex}][day]" required>
                    <option value="monday">Monday</option>
                    <option value="tuesday">Tuesday</option>
                    <option value="wednesday">Wednesday</option>
                    <option value="thursday">Thursday</option>
                    <option value="friday">Friday</option>
                    <option value="saturday">Saturday</option>
                    <option value="sunday">Sunday</option>
                </select>
                <input type="time" name="class_schedule[${scheduleIndex}][start_time]" required>
                <input type="time" name="class_schedule[${scheduleIndex}][end_time]" required>
                <select name="class_schedule[${scheduleIndex}][frequency]" required>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
                <button type="button" class="remove-schedule">-</button>
            </div>
        `;
        $('#schedule-container').append(newRow);
        scheduleIndex++;
    });

    $(document).on('click', '.remove-schedule', function() {
        $(this).closest('.schedule-row').remove();
    });
});
