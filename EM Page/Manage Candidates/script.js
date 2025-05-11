function addAchievement() {
    const achievementsDiv = document.getElementById('achievements');
    const newRow = document.createElement('div');
    newRow.classList.add('achievement-row');
    newRow.innerHTML = `
        <label>School: <input type="text" name="school[]" required></label>
        <label>Year: <input type="text" name="year[]" required></label>
        <label>Type of Achievement: <input type="text" name="achievement[]" required></label>
        <button type="button" onclick="removeAchievement(this)">🗑️</button>
    `;
    achievementsDiv.appendChild(newRow);
}

function removeAchievement(button) {
    button.parentElement.remove();
}

// Open Add Candidate Modal
document.getElementById('addCandidateBtn').addEventListener('click', () => {
    document.getElementById('addCandidateForm').reset();
    document.getElementById('addCandidateModal').style.display = 'flex';
});

function openEditModal(candidate) {
    document.getElementById('editCandidateNo').value = candidate.candidate_no;
    document.getElementById('editStudentId').value = candidate.student_id;
    document.getElementById('editPosition').value = candidate.position;
    document.getElementById('editPartylist').value = candidate.partylist_id;
    document.getElementById('editLastname').value = candidate.lastname;
    document.getElementById('editMiddlename').value = candidate.middlename;
    document.getElementById('editFirstname').value = candidate.firstname;
    document.getElementById('editAge').value = candidate.age;
    document.getElementById('editGender').value = candidate.gender;
    document.getElementById('editCourse').value = candidate.course;
    document.getElementById('editYearLevel').value = candidate.year_level;
    document.getElementById('editAchievements').value = candidate.achievements;

    // Display the modal
    const modal = document.getElementById('editCandidateModal');
    modal.style.display = 'flex';

    // Scroll the modal to the top
    modal.scrollTop = 0;
}

// Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function openDeleteModal(candidateNo) {
    document.getElementById('deleteCandidateNo').value = candidateNo;
    document.getElementById('deleteModal').style.display = 'flex';
}

let cropper;
let currentPictureInput = null; // Track which input is being cropped
const cropImageModal = document.getElementById('cropImageModal');
const cropImage = document.getElementById('cropImage');
const addCandidatePicture = document.getElementById('addCandidatePicture');
const editCandidatePicture = document.getElementById('editCandidatePicture');

function openCropModal(input) {
    currentPictureInput = input; // Remember which input triggered the crop
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            cropImage.src = e.target.result;
            if (cropper) cropper.destroy();
            cropper = new Cropper(cropImage, {
                aspectRatio: 1, // Square crop (1:1 aspect ratio)
                viewMode: 1,
                autoCropArea: 1,
            });
            cropImageModal.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Crop & Save button handler (works for both add and edit)
document.getElementById('cropButton').addEventListener('click', function() {
    if (cropper && currentPictureInput) {
        const canvas = cropper.getCroppedCanvas({
            width: 400, // Higher resolution for better quality
            height: 400,
            imageSmoothingQuality: 'high'
        });
        canvas.toBlob(function(blob) {
            const croppedFile = new File([blob], currentPictureInput.files[0].name, {
                type: 'image/jpeg',
            });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            currentPictureInput.files = dataTransfer.files; // Assign to the correct input
            closeModal('cropImageModal');
        }, 'image/jpeg', 0.95);
    }
});

addCandidatePicture.addEventListener('change', function() {
    openCropModal(this);
});
editCandidatePicture.addEventListener('change', function() {
    openCropModal(this);
});

function addCandidateToTable(candidate) {
    const table = document.getElementById('candidateTable');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${candidate.candidate_no}</td>
        <td>${candidate.student_id}</td>
        <td>${candidate.position}</td>
        <td>${candidate.lastname}</td>
        <td>${candidate.firstname}</td>
        <td>${candidate.middlename}</td>
        <td>${candidate.age}</td>
        <td>${candidate.gender}</td>
        <td>${candidate.course}</td>
        <td>${candidate.year_level}</td>
        <td>${candidate.achievements}</td>
        <td><img src="${candidate.candidate_picture || '../../Assets/Image/default-profile.png'}" alt="Candidate Picture" width="50"></td>
        <td>
            <button class="edit-btn" onclick="openEditModal(${JSON.stringify(candidate)})">✏️ Edit</button>
            <button class="delete-btn" onclick="openDeleteModal(${candidate.candidate_no})">🗑️ Delete</button>
        </td>
    `;
    table.appendChild(row);
}

function sortTable(columnIndex) {
    const table = document.getElementById("candidateTable");
    const rows = Array.from(table.rows);
    const isAscending = table.getAttribute("data-sort-order") === "asc";
    const direction = isAscending ? 1 : -1;

    rows.sort((a, b) => {
        const cellA = a.cells[columnIndex].textContent.trim().toLowerCase();
        const cellB = b.cells[columnIndex].textContent.trim().toLowerCase();

        if (!isNaN(cellA) && !isNaN(cellB)) {
            // Compare numbers
            return direction * (parseFloat(cellA) - parseFloat(cellB));
        } else {
            // Compare strings
            return direction * cellA.localeCompare(cellB);
        }
    });

    // Append sorted rows back to the table
    rows.forEach(row => table.appendChild(row));

    // Toggle sort order
    table.setAttribute("data-sort-order", isAscending ? "desc" : "asc");
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search');
    const table = document.getElementById('candidateTable');

    searchInput.addEventListener('input', () => {
        const filter = searchInput.value.toLowerCase();
        const rows = table.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let match = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toLowerCase().includes(filter)) {
                    match = true;
                    break;
                }
            }

            rows[i].style.display = match ? '' : 'none';
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const tableContainer = document.querySelector('.table-container');
    if (tableContainer) {
        tableContainer.classList.add('loaded'); // Add the 'loaded' class to make the table visible
    }
});