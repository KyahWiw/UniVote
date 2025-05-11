function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function openEditEventModal(event) {
    // Clear all fields in the modal
    document.getElementById('editEventId').value = '';
    document.getElementById('editEventName').value = '';
    document.getElementById('editOrganizedBy').value = '';
    document.getElementById('editEventDescription').value = '';
    document.getElementById('editEventStartDate').value = '';
    document.getElementById('editEventStartTime').value = '';
    document.getElementById('editEventEndDate').value = '';
    document.getElementById('editEventEndTime').value = '';
    document.getElementById('editVotingMode').value = '';
    document.getElementById('editPositions').value = '';
    document.getElementById('editEventType').value = '';

    // Populate the modal with the selected event's data
    document.getElementById('editEventId').value = event.id;
    document.getElementById('editEventName').value = event.event_name;
    document.getElementById('editOrganizedBy').value = event.organized_by;
    document.getElementById('editEventDescription').value = event.description;
    document.getElementById('editEventStartDate').value = event.event_datetime_start.split(' ')[0];
    document.getElementById('editEventStartTime').value = event.event_datetime_start.split(' ')[1];
    document.getElementById('editEventEndDate').value = event.event_datetime_end.split(' ')[0];
    document.getElementById('editEventEndTime').value = event.event_datetime_end.split(' ')[1];
    document.getElementById('editVotingMode').value = event.voting_mode;
    document.getElementById('editPositions').value = event.positions;
    document.getElementById('editEventType').value = event.event_type;

    // Open the modal
    document.getElementById('editEventModal').style.display = 'flex';
}

function openDeleteEventModal(eventId) {
    document.getElementById('deleteEventId').value = eventId;
    document.getElementById('deleteEventModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function updateTime() {
    let now = new Date();
    let options = {
        timeZone: "Asia/Manila",
        hour12: true,
        month: "long",
        day: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    };

    document.getElementById("liveTime").innerText = now.toLocaleString(
        "en-US",
        options
    );
}

// Update time every second
setInterval(updateTime, 1000);

// Initialize time on page load
updateTime();