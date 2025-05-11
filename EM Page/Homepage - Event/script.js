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

// Scroll to the right when the Next button is clicked
document.querySelector('.scroll-next').addEventListener('click', function() {
    const container = document.querySelector('.positions-container');
    container.scrollBy({
        left: 300, // Adjust scroll distance
        behavior: 'smooth',
    });
});

// Scroll to the left when the Previous button is clicked
document.querySelector('.scroll-previous').addEventListener('click', function() {
    const container = document.querySelector('.positions-container');
    container.scrollBy({
        left: -300, // Adjust scroll distance (negative for left)
        behavior: 'smooth',
    });
});

window.onload = function() {
    history.replaceState(null, null, location.href); // Replace the current state
    history.pushState(null, null, location.href); // Push a new state to the history stack

    window.onpopstate = function() {
        // Redirect to the homepage if the back button is pressed
        window.location.href = "../Homepage/index.php";
    };
};

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

function showCandidateProfile(candidate) {
    document.getElementById('profilePicture').src = '../../EM Page/Manage Candidates/' + candidate.candidate_picture;
    document.getElementById('profileName').textContent = candidate.firstname + ' ' + candidate.lastname;
    document.getElementById('profilePartylist').textContent = candidate.partylist;
    document.getElementById('profilePosition').textContent = candidate.position;
    document.getElementById('profileAbout').textContent = candidate.about || 'No additional information.'; // If you have an 'about' field
    document.getElementById('candidateProfileModal').style.display = 'block';
}

function closeCandidateProfile() {
    document.getElementById('candidateProfileModal').style.display = 'none';
}

// Optional: Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('candidateProfileModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}