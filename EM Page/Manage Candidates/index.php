<?php
session_start();
include "connect.php";

// Check if the user is logged in
if (!isset($_SESSION["student_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "electoral_manager") {
    header("Location: ../../Login page/Login/index.php");
    exit();
}

// Retrieve the user's name from the database
$header_user_name = "User"; // Default value
if (isset($_SESSION["student_id"])) {
    $student_id = $_SESSION["student_id"];
    $query = $db->prepare("SELECT firstname, lastname FROM accounts WHERE student_id = ?");
    $query->execute([$student_id]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $header_user_name = $user['firstname'] . ' ' . $user['lastname'];
    }
}

// Check if event_id is provided in the URL
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo "No election event selected.";
    exit();
}

$event_id = $_GET['event_id'];

// Fetch the election event details
$query = $db->prepare("SELECT * FROM election_events WHERE id = ?");
$query->execute([$event_id]);
$event = $query->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "Election event not found.";
    exit();
}

// Fetch partylists for the specific election event
$query = $db->prepare("SELECT * FROM partylists WHERE event_id = ? ORDER BY name ASC");
$query->execute([$event_id]);
$partylists = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch positions for the specific election event
$query = $db->prepare("SELECT * FROM positions WHERE event_id = ? ORDER BY position_order ASC");
$query->execute([$event_id]);
$positions = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch candidates for the specific election event
$query = $db->prepare("
    SELECT c.*, p.position_order 
    FROM candidates c
    LEFT JOIN positions p ON c.position = p.position_name AND p.event_id = c.event_id
    WHERE c.event_id = ? 
    ORDER BY p.position_order ASC, c.lastname ASC
");
$query->execute([$event_id]);
$candidates = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates | <?php echo htmlspecialchars($event['event_name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="../Homepage/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">
            <img class="logo" src="../../Assets/Image/logo-orig-white.png" alt="UniVote Logo">
        </a>
    </div>
    <div class="user-info">
        <span><?php echo htmlspecialchars($header_user_name); ?></span>
        <div class="user-avatar"></div>
    </div>
</header>

<div class="container">
    <aside>
        <nav>
            <h3>Quick Access</h3>
            <ul>
                <li><a href="../Homepage/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Homepage</a></li>
                <li class="active"><a href="#">Manage Candidates</a></li>
                <li><a href="../Manage Partylist/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Manage Partylist</a></li>
                <li><a href="../Voters Page/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Voters Page</a></li>
                <li><a href="../Settings - Event/index.php?event_id=<?php echo htmlspecialchars($event_id); ?>">Settings</a></li>
            </ul>
        </nav>
        <hr>
        <div class="notifications">
            <h3>Notifications</h3>
            <p>Not yet Available</p>
        </div>
    </aside>

    <main>
        <div class="main-content">
            <h2>Manage Candidates</h2>
            <hr>
            <div class="toolbar">
                <input type="text" id="search" placeholder="Search...">
                <button id="addCandidateBtn" onclick="openModal('addCandidateModal')">➕ Add Candidate</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Candidate No.</th>
                            <th>Student ID</th>
                            <th>Position</th>
                            <th>Partylist</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Achievements</th>
                            <th>Picture</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="candidateTable">
                        <?php if (empty($candidates)): ?>
                            <tr>
                                <td colspan="14" style="text-align: center;">No candidates found for this election event.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($candidates as $candidate): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($candidate['candidate_no']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['POSITION']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['partylist']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['firstname']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['middlename']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['age']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['course']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['year_level']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['achievements']); ?></td>
                                    <td>
                                        <div class="image-container">
                                            <?php if (!empty($candidate['candidate_picture']) && file_exists($candidate['candidate_picture'])): ?>
                                                <img src="<?php echo htmlspecialchars($candidate['candidate_picture']); ?>" alt="Candidate Picture">
                                            <?php else: ?>
                                                <img src="../../Assets/Image/default-profile.png" alt="Default Picture">
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($candidate)); ?>)">✏️ Edit</button>
                                        <button class="delete-btn" onclick="openDeleteModal(<?php echo htmlspecialchars($candidate['candidate_no']); ?>)">🗑️ Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Add Candidate Modal -->
<div id="addCandidateModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('addCandidateModal')">&times;</span>
        <h2>Add Candidate</h2>
        <form id="addCandidateForm" method="POST" action="add_candidate.php" enctype="multipart/form-data">
            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">
            <label>Student ID: <input type="text" name="student_id" id="addStudentId" required></label>
            <label>Position: 
                <select name="position" id="addPosition" required>
                    <option value="">Select Position</option>
                    <?php foreach ($positions as $position): ?>
                        <option value="<?php echo htmlspecialchars($position['position_name']); ?>">
                            <?php echo htmlspecialchars($position['position_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Partylist: 
                <select name="partylist_id" id="addPartylist" required>
                    <option value="">Select Partylist</option>
                    <?php foreach ($partylists as $partylist): ?>
                        <option value="<?php echo htmlspecialchars($partylist['partylist_id']); ?>">
                            <?php echo htmlspecialchars($partylist['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Last Name: <input type="text" name="lastname" id="addLastname" required></label>
            <label>First Name: <input type="text" name="firstname" id="addFirstname" required></label>
            <label>Middle Name: <input type="text" name="middlename" id="addMiddlename"></label>
            <label>Age: <input type="number" name="age" id="addAge" required></label>
            <label>Gender: 
                <select name="gender" id="addGender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </label>
            <label>Course: <input type="text" name="course" id="addCourse" required></label>
            <label>Year Level: 
                <select name="year_level" id="addYearLevel" required>
                    <option value="1st">1st</option>
                    <option value="2nd">2nd</option>
                    <option value="3rd">3rd</option>
                    <option value="4th">4th</option>
                </select>
            </label>
            <label>Achievements: <textarea name="achievements" id="addAchievements" rows="3"></textarea></label>
            <label>Picture: <input type="file" name="candidate_picture" id="addCandidatePicture" accept="image/*" onchange="openCropModal(this)"></label>

            <div class="modal-actions">
                <button type="submit" class="add-btn">Add Candidate</button>
                <button type="button" class="cancel-btn" onclick="closeModal('addCandidateModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Candidate Modal -->
<div id="editCandidateModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('editCandidateModal')">&times;</span>
        <h2>Edit Candidate</h2>
        <form id="editCandidateForm" method="POST" action="edit_candidate.php" enctype="multipart/form-data">
            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">
            <input type="hidden" name="candidate_no" id="editCandidateNo">
            <label>Student ID: <input type="text" name="student_id" id="editStudentId" required></label>
            <label>Position: 
                <select name="position" id="editPosition" required>
                    <option value="">Select Position</option>
                    <?php foreach ($positions as $position): ?>
                        <option value="<?php echo htmlspecialchars($position['position_name']); ?>">
                            <?php echo htmlspecialchars($position['position_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Partylist: 
                <select name="partylist_id" id="editPartylist" required>
                    <option value="">Select Partylist</option>
                    <?php foreach ($partylists as $partylist): ?>
                        <option value="<?php echo htmlspecialchars($partylist['partylist_id']); ?>">
                            <?php echo htmlspecialchars($partylist['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Last Name: <input type="text" name="lastname" id="editLastname" required></label>
            <label>First Name: <input type="text" name="firstname" id="editFirstname" required></label>
            <label>Middle Name: <input type="text" name="middlename" id="editMiddlename"></label>
            <label>Age: <input type="number" name="age" id="editAge" required></label>
            <label>Gender: 
                <select name="gender" id="editGender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </label>
            <label>Course: <input type="text" name="course" id="editCourse" required></label>
            <label>Year Level: 
                <select name="year_level" id="editYearLevel" required>
                    <option value="1st">1st</option>
                    <option value="2nd">2nd</option>
                    <option value="3rd">3rd</option>
                    <option value="4th">4th</option>
                </select>
            </label>
            <label>Achievements: <textarea name="achievements" id="editAchievements" rows="3"></textarea></label>
            <label>Picture: <input type="file" name="candidate_picture" id="editCandidatePicture" accept="image/*" onchange="openCropModal(this)"></label>
            <div id="currentPictureContainer" style="margin-top: 10px; display: none;">
                <p>Current Picture:</p>
                <img id="currentPicturePreview" src="" style="max-width: 100px; max-height: 100px; display: block; margin: 5px 0;">
                <button type="button" id="deletePictureBtn" class="delete-btn" style="margin-top: 5px;">Delete Picture</button>
            </div>
            <div class="modal-actions">
                <button type="submit" class="edit-btn">Save Changes</button>
                <button type="button" class="cancel-btn" onclick="closeModal('editCandidateModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('deleteModal')">&times;</span>
        <h2>Confirm Delete</h2>
        <p>Are you sure you want to delete this candidate?</p>
        <form id="deleteForm" method="POST" action="delete_candidate.php">
    <input type="hidden" name="candidate_no" id="deleteCandidateNo">
    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">
    <div class="modal-actions">
        <button type="submit" class="delete-btn">Delete</button>
        <button type="button" class="cancel-btn" onclick="closeModal('deleteModal')">Cancel</button>
    </div>
</form>
    </div>
</div>

<!-- Crop Image Modal -->
<div id="cropImageModal" class="modal" role="dialog" aria-modal="true" tabindex="-1">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('cropImageModal')" tabindex="0" aria-label="Close">&times;</span>
        <h2>Crop Image</h2>
        <div class="crop-container">
            <img id="cropImage" src="" alt="Image to Crop">
        </div>
        <div class="modal-actions">
            <button id="cropButton" class="save-btn">Crop & Save</button>
            <button type="button" class="cancel-btn" onclick="closeModal('cropImageModal')">Cancel</button>
        </div>
    </div>
</div>

<script src="script.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
</body>
</html>