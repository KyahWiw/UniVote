function showPosition(positionId) {
    // Hide all position groups
    const positionGroups = document.querySelectorAll('.position-group');
    positionGroups.forEach(group => group.classList.remove('active'));

    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(tab => tab.classList.remove('active'));

    // Show the selected position group
    document.getElementById(positionId).classList.add('active');

    // Highlight the active tab
    const activeTab = Array.from(tabs).find(tab => tab.textContent.trim() === positionId);
    if (activeTab) activeTab.classList.add('active');
}