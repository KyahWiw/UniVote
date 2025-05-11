document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("search-btn").addEventListener("click", function() {
        alert("Search function is not implemented yet.");
    });

    document.querySelector(".view-all").addEventListener("click", function() {
        alert("Redirecting to the Election Event page...");
        window.location.href = "election-event.php";
    });
});

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("currentDate").innerText = new Date().toDateString();
});

function addEvent() {
    let eventInput = document.getElementById("eventInput").value;
    let eventList = document.getElementById("eventList");

    if (eventInput.trim() === "") {
        alert("Please enter an event name.");
        return;
    }

    if (eventList.innerHTML.includes("No events added yet.")) {
        eventList.innerHTML = "";
    }

    let newEvent = document.createElement("li");
    newEvent.textContent = eventInput;
    eventList.appendChild(newEvent);

    document.getElementById("eventInput").value = "";
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

// Function to open a modal
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

// Function to close a modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Function to open the Edit Account modal and populate fields
function openEditModal(account) {
    document.getElementById('editAccountNo').value = account.account_id;
    document.getElementById('editStudentId').value = account.student_id;
    document.getElementById('editLastName').value = account.lastname;
    document.getElementById('editFirstName').value = account.firstname;
    document.getElementById('editMiddleName').value = account.middlename;
    document.getElementById('editYearLevel').value = account.year_level;
    document.getElementById('editDepartment').value = account.department;
    document.getElementById('editCourse').value = account.course;
    document.getElementById('editRole').value = account.role;
    document.getElementById('editEmail').value = account.email;
    closeModal('addAccountModal'); // Ensure the Add Account modal is closed
    openModal('editAccountModal'); // Open the Edit Account modal
}

// Function to open the Delete Account modal and set the account ID
function openDeleteModal(accountNo) {
    document.getElementById('deleteAccountNo').value = accountNo;
    openModal('deleteAccountModal'); // Open the Delete Account modal
}

// Close modals when clicking outside of them
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach((modal) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
};

function openDeleteModal(accountNo) {
    document.getElementById('deleteAccountNo').value = accountNo;
    openModal('deleteAccountModal');
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.toggle-table-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.table-password-input');
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');
            if (input.type === "password") {
                input.type = "text";
                eyeOpen.style.display = "none";
                eyeClosed.style.display = "inline";
            } else {
                input.type = "password";
                eyeOpen.style.display = "inline";
                eyeClosed.style.display = "none";
            }
        });
    });
});