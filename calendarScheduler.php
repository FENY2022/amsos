<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RICTU Calendar Scheduler</title>

    <!-- FullCalendar CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet"> -->

    <!-- Bootstrap CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>

        #calendar {
            max-width: 900px;
            margin: 2rem auto;
        }
    </style>
</head>
<body>
    <h2 class="text-center">Calendar Scheduler Reminder</h2>

    <div class="container my-5">
        <h2 class="mb-4 text-center">Event Scheduler</h2>
        <!-- Button to Open Modal -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal">
            Add New Event
        </button>
    </div>


    <!-- Event Form Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Add New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="add_event.php" method="POST">
                        <div class="mb-3">
                            <label for="event_date" class="form-label">Date</label>
                            <input type="date" class="form-control" name="event_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="zoom_link" class="form-label">Zoom Link</label>
                            <input type="url" class="form-control" name="zoom_link">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="text" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    

<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailsModalLabel">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-event-id"> <!-- Hidden Event ID -->

                <!-- View Mode -->
                <div id="eventDetailsView">
                    <p><strong>Date:</strong> <span id="modal-event-date"></span></p>
                    <p><strong>Remarks:</strong> <span id="modal-event-remarks"></span></p>
                    <p><strong>Zoom Link:</strong> <a id="modal-event-zoom-link" href="#" target="_blank"></a></p>
                    <p><strong>Password:</strong> <span id="modal-event-password"></span></p>
                    <p><strong>Email:</strong> <span id="modal-event-email"></span></p>
                </div>

                <!-- Edit Mode (Hidden by Default) -->
                <form id="editEventForm" style="display: none;">
                    <div class="mb-3">
                        <label for="edit-event-date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit-event-date">
                    </div>
                    <div class="mb-3">
                        <label for="edit-event-remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="edit-event-remarks"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-event-zoom-link" class="form-label">Zoom Link</label>
                        <input type="url" class="form-control" id="edit-event-zoom-link">
                    </div>
                    <div class="mb-3">
                        <label for="edit-event-password" class="form-label">Password</label>
                        <input type="text" class="form-control" id="edit-event-password">
                    </div>
                    <div class="mb-3">
                        <label for="edit-event-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit-event-email">
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="deleteEventBtn">Delete</button>
                <button type="button" class="btn btn-primary" id="editEventBtn">Edit</button>
                <button type="button" class="btn btn-success" id="saveChangesBtn" style="display: none;">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('editEventBtn').addEventListener('click', function() {
    document.getElementById('eventDetailsView').style.display = 'none';
    document.getElementById('editEventForm').style.display = 'block';
    document.getElementById('saveChangesBtn').style.display = 'inline-block';
    this.style.display = 'none';

    // Populate edit form with current values
    document.getElementById('edit-event-date').value = document.getElementById('modal-event-date').textContent;
    document.getElementById('edit-event-remarks').value = document.getElementById('modal-event-remarks').textContent;
    document.getElementById('edit-event-zoom-link').value = document.getElementById('modal-event-zoom-link').href;
    document.getElementById('edit-event-password').value = document.getElementById('modal-event-password').textContent;
    document.getElementById('edit-event-email').value = document.getElementById('modal-event-email').textContent;
});

document.getElementById('saveChangesBtn').addEventListener('click', function() {
    var eventId = document.getElementById('modal-event-id').value;
    var eventData = {
        id: eventId,
        date: document.getElementById('edit-event-date').value,
        remarks: document.getElementById('edit-event-remarks').value,
        zoom_link: document.getElementById('edit-event-zoom-link').value,
        password: document.getElementById('edit-event-password').value,
        email: document.getElementById('edit-event-email').value
    };

    fetch('update_event.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(eventData)
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        location.reload();
    })
    .catch(error => console.error('Error:', error));
});

document.getElementById('deleteEventBtn').addEventListener('click', function() {
    var eventId = document.getElementById('modal-event-id').value;

    if (confirm('Are you sure you want to delete this event?')) {
        fetch('delete_event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: eventId })
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload();
        })
        .catch(error => console.error('Error:', error));
    }
});
</script>


    <!-- Calendar -->
    <div id="calendar"></div>

    <!-- FullCalendar & Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
 

        document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: 'fetch_events.php',
        eventClick: function(info) {
            // Fetch event details from `info.event.extendedProps`
            $('#modal-event-date').text(info.event.start.toISOString().split('T')[0]);
            $('#modal-event-remarks').text(info.event.title);
            $('#modal-event-zoom-link').text(info.event.extendedProps.zoom_link).attr('href', info.event.extendedProps.zoom_link);
            $('#modal-event-password').text(info.event.extendedProps.password);
            $('#modal-event-email').text(info.event.extendedProps.email);

            // Show the modal
            $('#eventDetailsModal').modal('show');
        }
    });

    calendar.render();
});

    </script>
</body>
</html>
