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