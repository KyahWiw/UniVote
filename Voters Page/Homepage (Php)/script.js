document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("search-btn").addEventListener("click", function () {
    alert("Search function is not implemented yet.");
  });

  document.querySelector(".view-all").addEventListener("click", function () {
    alert("Redirecting to the Election Event page...");
    window.location.href = "../Voting Page/index.php";
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
