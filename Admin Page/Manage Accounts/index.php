<?php
session_start();
include "connect.php";

// Check if the user is logged in
if (!isset($_SESSION["student_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../Login page/Login/index.php");
    exit();
}

// Retrieve the user's name from the database
$user_name = "User"; // Default value
if (isset($_SESSION["student_id"])) {
    $student_id = $_SESSION["student_id"];
    $query = $db->prepare("SELECT * FROM accounts WHERE student_id = ?");
    $query->execute([$student_id]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $header_user_name = $user['firstname'] . ' ' . $user['lastname'];
    }
}

// Fetch all accounts from the database
$query = $db->prepare("SELECT * FROM accounts ORDER BY account_id ASC");
$query->execute();
$accounts = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts | UniVote </title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="success-message">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        <?php unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
        <?php unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>
<body>

<header>
    <div class="logo">
        <img class="logo" src="..\..\Assets\Image\logo-orig-white.png">
    </div>
    <div class="user-info">
        <span><?php echo htmlspecialchars($header_user_name); ?></span>
        <div class="user-avatar"></div>
    </div>
</header>

<div class="container">
    <aside>
    <?php
        echo '<nav>
        <nav>
            <h3>Quick Access</h3>
            <ul>
               <li><a href="../Homepage/index.php">Homepage</a></li>
                <li class="active"><a href="#">Manage Accounts</a></li>
                <li><a href="../Election Event/index.php">Election Events</a></li>
                <li><a href="-">Settings</a></li>
                <li><a href="../../Login page/Login/logout.php">Log Out</a></li>
            </ul>
        </nav>';
    ?>
        <hr>
        <div class="notifications">
            <h3>Notifications</h3>
            <p> Not Yet Available </p>
        </div>
    </aside>

    <main>
            <h2>Manage Accounts</h2>
            <hr>
            <div class="search-bar">
                <input type="text" placeholder="Search">
                <button class="search-btn">🔍</button>
                <button class="filter-btn">⚙️ Filter</button>
                <button class="add-btn" onclick="openModal('addAccountModal')">➕ Add New Account</button>
                <form action="edit_account.php" method="POST" style="display: inline;">
                <a href="export_accounts.php" class="btn btn-info">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importAccountModal">
                    <i class="fas fa-file-import"></i> Import from Excel
                </button>
            </div>
            

            <table class="accounts-table">
    <thead>
        <tr>
            <th>Account No.</th>
            <th>Student ID</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Year Level</th>
            <th>Department</th>
            <th>Course</th>
            <th>Role</th>
            <th>Email</th>
            <th>Password</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($accounts as $account): ?>
            <tr>
                <td><?php echo htmlspecialchars($account['account_id']); ?></td>
                <td><?php echo htmlspecialchars($account['student_id']); ?></td>
                <td><?php echo htmlspecialchars($account['lastname']); ?></td>
                <td><?php echo htmlspecialchars($account['firstname']); ?></td>
                <td><?php echo htmlspecialchars($account['middlename']); ?></td>
                <td><?php echo htmlspecialchars($account['year_level']); ?></td>
                <td><?php echo htmlspecialchars($account['department']); ?></td>
                <td><?php echo htmlspecialchars($account['course']); ?></td>
                <td><?php echo htmlspecialchars($account['role']); ?></td>
                <td><?php echo htmlspecialchars($account['email']); ?></td>
                <td>
    <div class="password-table-container">
        <input type="password" class="table-password-input" value="<?php echo htmlspecialchars($account['password']); ?>" readonly>
        <button type="button" class="toggle-table-password" aria-label="Show password">
            <span class="eye-open">
                <!-- Eye open SVG -->
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="12" rx="9" ry="5"/>
                    <circle cx="12" cy="12" r="2"/>
                </svg>
            </span>
            <span class="eye-closed" style="display:none;">
                <!-- Eye closed SVG -->
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="12" rx="9" ry="5"/>
                    <circle cx="12" cy="12" r="2"/>
                    <line x1="4" y1="20" x2="20" y2="4" />
                </svg>
            </span>
        </button>
    </div>
</td>
                <td>
                    <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($account)); ?>)">✏️ Edit</button>
                    <button class="delete-btn" onclick="openDeleteModal(<?php echo $account['account_id']; ?>)">🗑️ Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    </main>
</div>

<!-- Add New Account Modal -->
<div id="addAccountModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('addAccountModal')">&times;</span>
        <h2>Add New Account</h2>
        <form method="POST" action="add_account.php">
            <label for="student_id">Student ID:</label>
            <input type="text" name="student_id" required>
            
            <label for="lastname">Last Name:</label>
            <input type="text" name="lastname" required>
            
            <label for="firstname">First Name:</label>
            <input type="text" name="firstname" required>

            <label for="middlename">Middle Name:</label>
            <input type="text" name="middlename">

            <label for="year_level">Year Level:</label>
            <select name="year_level" required>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
                <option value="Irregular">Irregular</option>
            </select>
            
            <label for="department">Department:</label>
            <select name="department" required>
                <option value="CAS">College of Arts and Science (CAS)</option>
                <option value="CITE">College of Information Technology Education (CITE)</option>
                <option value="CMLS">College of Medical Laboratory Science (CMLS)</option>
                <option value="CON">College of Nursing (CON)</option>
                <option value="SBC">School of Business Commerce (SBC)</option>
                <option value="SOE">School of Education (SOE)</option>
                <option value="SHTM">School of Hospitality and Tourism Management (SHTM)</option>
            </select>

            <label for="course">Course:</label>
            <input type="text" name="course" required>
            
            <label for="role">Role:</label>
            <select name="role" required>
                <option value="voter">Voter</option>
                <option value="admin">Admin</option>
                <option value="electoral_manager">Electoral Manager</option>
            </select>
            
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Add Account</button>
        </form>
    </div>
</div>

<!-- Edit Account Modal -->
<div id="editAccountModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('editAccountModal')">&times;</span>
        <h2>Edit Account</h2>
        <form method="POST" action="edit_account.php">
    <input type="hidden" name="account_no" id="editAccountNo">
            
            <label for="student_id">Student ID:</label>
            <input type="text" name="student_id" id="editStudentId" required>
            
            <label for="lastname">Last Name:</label>
            <input type="text" name="lastname" id="editLastName" required>
            
            <label for="firstname">First Name:</label>
            <input type="text" name="firstname" id="editFirstName" required>
            
            <label for="middlename">Middle Name:</label>
            <input type="text" name="middlename" id="editMiddleName">

            <label for="year_level">Year Level:</label>
            <select name="year_level" id="editYearLevel"required>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
                <option value="Irregular">Irregular</option>
            </select>
            
            <label for="department">Department:</label>
            <select name="department" id="editDepartment" required>
                <option value="CAS">College of Arts and Science (CAS)</option>
                <option value="CITE">College of Information Technology Education (CITE)</option>
                <option value="CMLS">College of Medical Laboratory Science (CMLS)</option>
                <option value="CON">College of Nursing (CON)</option>
                <option value="SBC">School of Business Commerce (SBC)</option>
                <option value="SOE">School of Education (SOE)</option>
                <option value="SHTM">School of Hospitality and Tourism Management (SHTM)</option>
            </select>
            
            <label for="course">Course:</label>
            <input type="text" name="course" id="editCourse" required>
            
            <label for="role">Role:</label>
            <select name="role" id="editRole" required>
                <option value="voter">Voter</option>
                <option value="admin">Admin</option>
                <option value="electoral_manager">Electoral Manager</option>
            </select>
            
            <label for="email">Email:</label>
            <input type="email" name="email" id="editEmail" required>
            
            <label for="password">Password (leave blank to keep current password):</label>
            <input type="password" name="password" id="editPassword">
            
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteAccountModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('deleteAccountModal')">&times;</span>
        <h2>Confirm Delete</h2>
        <p>Are you sure you want to delete this account?</p>
        <form method="POST" action="delete_account.php">
            <input type="hidden" name="account_no" id="deleteAccountNo">
            <button type="submit" class="delete-btn">Delete</button>
            <button type="button" class="cancel-btn" onclick="closeModal('deleteAccountModal')">Cancel</button>
        </form>
    </div>
</div>

<div class="modal fade" id="importAccountModal" tabindex="-1" aria-labelledby="importAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="import_accounts.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="importAccountModalLabel">Import Accounts from Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Select Excel File</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <strong>Note:</strong> The Excel file should have the following columns in order:<br>
                            Account ID, Student ID, Last Name, First Name, Middle Name, Year Level, Department, Course, Role, Email, Password
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>