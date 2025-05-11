document.addEventListener("DOMContentLoaded", function() {
    // Fetch voting results from server
    fetchVotingResults();

    // Set up periodic refresh (every 30 seconds)
    setInterval(fetchVotingResults, 30000);
});

function fetchVotingResults() {
    const eventId = new URLSearchParams(window.location.search).get('event_id');
    if (!eventId) return;

    fetch(`fetch_results.php?event_id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            updateResultsDisplay(data);
            updateVoteChart(data);
        })
        .catch(error => console.error('Error fetching results:', error));
}

function updateResultsDisplay(results) {
    // This will be handled by the server-side rendering
    console.log('Results updated:', results);
}

function updateVoteChart(results) {
    const ctx = document.getElementById("voteChart").getContext("2d");

    // Group results by position
    const positions = [...new Set(results.map(r => r.position))];

    // Create dataset for each position
    const datasets = positions.map(position => {
        const posResults = results.filter(r => r.position === position);
        return {
            label: position,
            data: posResults.map(r => r.vote_count),
            backgroundColor: getRandomColor(),
            borderColor: getRandomColor(),
            borderWidth: 1
        };
    });

    // Get candidate names for labels
    const labels = [...new Set(results.map(r =>
        `${r.candidate_firstname} ${r.candidate_lastname} (${r.partylist_name})`
    ))];

    // Create or update chart
    if (window.voteChart) {
        window.voteChart.data.labels = labels;
        window.voteChart.data.datasets = datasets;
        window.voteChart.update();
    } else {
        window.voteChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { color: "rgba(0,0,0,0.1)" } },
                    y: {
                        grid: { color: "rgba(0,0,0,0.1)" },
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

function getRandomColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}