let selectedCandidate = null;

function selectCandidate(candidateId) {
    // Remove previous selection
    let cards = document.querySelectorAll(".candidate-card");
    cards.forEach((card) => (card.style.border = "none"));

    // Highlight selected
    selectedCandidate = candidateId;
    let selectedCard = cards[candidateId - 1];
    selectedCard.style.border = "3px solid green";

    console.log("Selected Candidate ID: " + candidateId);
}

// Handle Next Button Click
document.querySelector(".next-btn").addEventListener("click", function() {
    if (selectedCandidate) {
        alert("You voted for Candidate " + selectedCandidate);
        // Submit vote to server (AJAX or PHP processing)
    } else {
        alert("Please select a candidate before proceeding.");
    }
});

function showPosition(positionId) {
    // Hide all position groups
    const positionGroups = document.querySelectorAll('.position-group');
    positionGroups.forEach(group => {
        group.classList.remove('active');
    });

    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });

    // Show the selected position group
    const selectedGroup = document.getElementById(positionId);
    if (selectedGroup) {
        selectedGroup.classList.add('active');
    }

    // Highlight the selected tab
    const selectedTab = document.querySelector(`.tab-btn[onclick="showPosition('${positionId}')"]`);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
}

// Function to open the double-check modal
function openDoubleCheckModal() {
    const modal = document.getElementById('doubleCheckModal');
    const selectedCandidatesContainer = document.getElementById('selectedCandidates');
    selectedCandidatesContainer.innerHTML = ''; // Clear previous content

    // Get all selected candidates
    const selectedInputs = document.querySelectorAll('input[type="radio"]:checked');
    if (selectedInputs.length === 0) {
        alert('Please select at least one candidate before submitting.');
        return;
    }

    // Populate the modal with selected candidates
    selectedInputs.forEach((input) => {
        const candidateCard = input.closest('.candidate-card');
        const candidateName = candidateCard.querySelector('strong').textContent;
        const partylistName = candidateCard.querySelector('.partylist-name').textContent;
        const positionName = candidateCard.closest('.position-group').querySelector('h3').textContent;

        const candidateInfo = document.createElement('p');
        candidateInfo.innerHTML = `<strong>${positionName}:</strong> ${candidateName} (${partylistName})`;
        selectedCandidatesContainer.appendChild(candidateInfo);
    });

    // Show the modal
    modal.style.display = 'block';
}

// Function to close the modal
function closeModal() {
    const modal = document.getElementById('doubleCheckModal');
    modal.style.display = 'none';
}

// Function to confirm the vote
function confirmVote() {
    document.getElementById('voteForm').submit(); // Submit the form
}

// Function to open the confirmation modal
function openConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    modal.style.display = 'block';
}

// Function to redirect to the election results page
function redirectToResults() {
    const eventId = document.querySelector('input[name="event_id"]').value;
    window.location.href = `../Voting Result Page/index.php?event_id=${eventId}`;
}

// Function to confirm the vote and show the confirmation modal
function confirmVote() {
    const voteForm = document.getElementById('voteForm');
    voteForm.action = "submit_vote.php"; // Set the form action to the backend script
    voteForm.method = "POST"; // Ensure the form uses POST method

    // Simulate form submission and show the confirmation modal
    voteForm.submit();
    openConfirmationModal();
}