<?php
session_start();
// Security check
if (!isset($_SESSION['admin_username'])) {
    header("Location: adminlogin.php");
    exit;
}

include 'db_connect.php'; 

$msg = ''; // Initialize message variable

/* ========== ADD ADMIN (CREATE) ========== */
if (isset($_POST['add_admin'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Use prepared statements for security
    $check = $conn->prepare("SELECT * FROM admin_tbl WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $msg = "⚠️ Username already exists! Please choose a different one.";
    } else {
        $stmt = $conn->prepare("INSERT INTO admin_tbl (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);
        if ($stmt->execute()) {
            $msg = "✅ New admin added successfully!";
        } else {
            $msg = "❌ Error adding admin: " . $stmt->error;
        }
    }
}

/* ========== DELETE ADMIN ========== */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Prevent the currently logged-in admin from deleting themselves (optional but recommended)
    if ($id == ($_SESSION['admin_id'] ?? 0)) {
         $msg = "🛑 You cannot delete your own active account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM admin_tbl WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $msg = "🗑️ Admin deleted successfully!";
        } else {
            $msg = "❌ Error deleting admin: " . $stmt->error;
        }
    }
}

/* ========== UPDATE ADMIN ========== */
if (isset($_POST['update_admin'])) {
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin_tbl SET username=?, password=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $hashed, $id);
    } else {
        $stmt = $conn->prepare("UPDATE admin_tbl SET username=? WHERE id=?");
        $stmt->bind_param("si", $username, $id);
    }
    
    if ($stmt->execute()) {
        $msg = "✅ Admin updated successfully!";
    } else {
        $msg = "❌ Error updating admin: " . $stmt->error;
    }
}

/* ========== FETCH ALL ADMINS (READ) ========== */
$result = $conn->query("SELECT id, username, password FROM admin_tbl ORDER BY id DESC");


// --- INCLUDE HEADER (contains all the main CSS and starts the HTML structure) ---
include 'includes/header.php';
?>

<style>
/* --- PAGE-SPECIFIC STYLES --- */
.content-area {
    /* Using the styles from header.php's content-area or similar class */
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    margin-top: 30px;
}

h2 {
    color: var(--primary-dark); /* #03396c */
    margin-bottom: 25px;
    font-weight: 500;
}

.msg {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
    /* Custom styling for success/error based on your palette */
    background: #EAF5F5; /* Light teal/seafoam */
    color: var(--primary-dark);
    border: 1px solid var(--action-color);
}

/* Form Styles */
.form-container {
    background: #f8f8f8;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    border-left: 5px solid var(--action-color);
}

.form-container form {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap; /* Allows wrapping on smaller screens */
}

input[type="text"], input[type="password"] {
    flex: 1; /* Allows inputs to grow */
    min-width: 200px;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    transition: border-color 0.3s;
}

input[type="text"]:focus, input[type="password"]:focus {
    border-color: var(--primary-light); /* #005b96 */
    outline: none;
}

/* Button Styles (Using action_color) */
button {
    padding: 12px 25px;
    background: var(--action-color); /* #6F9F9C */
    border: none;
    color: #fff;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.3s ease, transform 0.15s ease, box-shadow 0.3s ease;
}

button:hover {
    background: var(--action-hover); /* #5d8c89 */
    transform: translateY(-2px); /* Mouse point new hover animation */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

#editForm button[type="button"] { /* Cancel Button */
    background: #95a5a6; 
}
#editForm button[type="button"]:hover {
    background: #7f8c8d;
    transform: none;
    box-shadow: none;
}

/* Table Styles */
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.data-table th, .data-table td {
    text-align: left;
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.data-table th {
    background: var(--primary-dark); /* #03396c */
    color: #fff;
    text-transform: uppercase;
    font-size: 0.9em;
}

.data-table tr:nth-child(even) {
    background: #fcfcfc;
}

/* Scrollable Project Table (4 rows visible) */
.table-scroll {
    max-height: 350px; /* Shows ~4 projects */
    overflow-y: auto;
    border-radius: 8px;
}

/* Keep table header fixed while scrolling */
.table-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 2;
}


/* Action Links */
.actions a {
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 5px;
    font-size: 0.9em;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: background 0.3s;
}
.actions a.edit {
    background: var(--primary-light); /* #005b96 */
    color: white;
}
.actions a.delete {
    background: #e74c3c;
    color: white;
}
.actions a:hover {
    opacity: 0.8;
}

</style>

<div class="content-area">
    <h2>Admin Management</h2>

    <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <div class="form-container">
        <h3>Add New Admin</h3>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_admin">Add Admin</button>
        </form>
    </div>

    <div class="table-scroll">
        <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Password (Hashed)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['username']); ?></td>
                <td style="font-family: monospace; font-size: 0.8em; color: #555;"><?= substr(htmlspecialchars($row['password']), 0, 30) . '...'; ?></td>
                <td class="actions">
                    <a href="#" class="edit" onclick="editAdmin(<?= $row['id']; ?>, '<?= htmlspecialchars($row['username']); ?>')"><i class="fas fa-edit"></i> Edit</a>
                    <a href="?delete=<?= $row['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete admin: <?= htmlspecialchars($row['username']); ?>?');"><i class="fas fa-trash-alt"></i> Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>

    <div class="form-container" id="editForm" style="display:none;">
        <h3>Update Admin</h3>
        <form method="POST" action="">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="username" id="edit_username" placeholder="Update Username" required>
            <input type="password" name="password" placeholder="New Password (optional)">
            <button type="submit" name="update_admin">Update Admin</button>
            <button type="button" onclick="cancelEdit()">Cancel</button>
        </form>
    </div>
</div>

<script>
// Reuse the JavaScript for the Edit form toggle
function editAdmin(id, username) {
    document.getElementById("editForm").style.display = "block";
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_username").value = username;
    // Scroll to the edit form for better UX
    document.getElementById("editForm").scrollIntoView({ behavior: 'smooth' });
}
function cancelEdit() {
    document.getElementById("editForm").style.display = "none";
}
</script>

<?php 
// --- CLOSE DB CONNECTION and INCLUDE FOOTER ---
$conn->close();
include 'includes/footer.php'; 
?>