<?php
session_start();
// --- SECURITY CHECK (Essential) ---
if (!isset($_SESSION['admin_username'])) {
    header("Location: adminlogin.php");
    exit;
}

// Ensure db_connect.php is in the same directory as this script, or adjust the path
include 'db_connect.php'; 

// Initialize messages
$success = '';
$error = '';

// --- FETCH PROJECTS COUNT (Crucial check for deletion) ---
// Function to check if category is in use
function isCategoryInUse($conn, $categoryId) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM project_tbl WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count;
}

// --- ADD CATEGORY ---
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        // Check for duplicate (best practice)
        $check_query = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $check_query->bind_param("s", $name);
        $check_query->execute();
        $check_query->store_result();
        if ($check_query->num_rows > 0) {
            $error = "Category already exists.";
        } else {
            $query = "INSERT INTO categories (name) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $success = "✅ Category added successfully!";
            } else {
                $error = "❌ Error adding category: " . $conn->error;
            }
        }
    } else {
        $error = "⚠️ Please enter a category name.";
    }
}

// --- UPDATE CATEGORY ---
if (isset($_POST['update_category'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $query = "UPDATE categories SET name=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            $success = "✅ Category updated successfully!";
        } else {
            $error = "❌ Error updating category: " . $conn->error;
        }
    } else {
        $error = "⚠️ Category name cannot be empty.";
    }
}

// --- DELETE CATEGORY ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Check if category is currently used by any project
    if (isCategoryInUse($conn, $id) > 0) {
         $error = "🛑 Cannot delete category! Projects are currently assigned to it.";
    } else {
        $query = "DELETE FROM categories WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "🗑️ Category deleted successfully!";
        } else {
            $error = "❌ Error deleting category: " . $conn->error;
        }
    }
}

// --- FETCH CATEGORIES ---
// Added 'created_at' in query assuming your table has it
$result = $conn->query("SELECT id, name, created_at FROM categories ORDER BY id DESC");


// --- TEMPLATING: START DASHBOARD LAYOUT ---
// Include your main header which contains the sidebar logic and CSS
include 'includes/header.php'; 
?>

<style>
/* Use content-area class for the main page wrapper */
.content-area {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: var(--shadow-light); /* from header.php */
    margin-top: 30px;
}
h2 {
    color: var(--primary-dark);
    margin-bottom: 25px;
    font-weight: 500;
}
.msg-success {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
    background: #e6ffee; /* Light Green */
    color: #155724;
    border: 1px solid #c3e6cb;
}
.msg-error {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
    background: #ffe6e6; /* Light Red */
    color: #721c24;
    border: 1px solid #f5c6cb;
}
/* Form/Card Styling */
.form-container, .table-card {
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
}
input[type="text"] {
    flex: 1;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
}
/* Table Styling */
.data-table {
    width: 100%;
    border-collapse: collapse;
}
.data-table th {
    background: var(--primary-dark);
    color: #fff;
    padding: 15px;
    text-align: left;
}
.data-table td {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}
.data-table tr:nth-child(even) {
    background: #fcfcfc;
}

/* Scrollable category table */
.table-scroll {
    max-height: 520px; /* ~7 rows visible */
    overflow-y: auto;
    border-radius: 8px;
}

/* Keep table header fixed while scrolling */
.table-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 2;
}


/* Button Styling (Using primary and action colors) */
.btn-primary-action {
    background: var(--action-color); 
    border-color: var(--action-color);
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    transition: all 0.3s ease;
}
.btn-primary-action:hover {
    background: var(--action-hover);
    border-color: var(--action-hover);
    transform: translateY(-2px); 
}
.btn-delete {
    background: #e74c3c;
    color: white;
}
.btn-delete:hover {
    background: #c0392b;
}
.btn-edit {
    background: var(--primary-light);
    color: white;
}
.btn-edit:hover {
    background: var(--primary-dark);
}

/* Modal adjustments (if using a dashboard without Bootstrap's JS/CSS) */
.modal-dialog { max-width: 500px; margin: 1.75rem auto; }

</style>

<div class="content-area">
    <h2>Category Management</h2>

    <?php if (!empty($success)): ?>
        <div class="msg-success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="msg-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-container">
        <h3>Add New Category</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Enter category name (e.g., Web Design)" required>
            <button type="submit" name="add_category" class="btn-primary-action">Add Category</button>
        </form>
    </div>

    <div class="table-scroll">
        <h3>Category List</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 50%;">Name</th>
                    <th style="width: 20%;">Date Added</th>
                    <th style="width: 25%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                        <td style="text-align: center;">
                            <button class="btn-primary-action btn-edit btn-sm" 
                                    onclick="showEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="?delete=<?= $row['id'] ?>" 
                               class="btn-primary-action btn-delete btn-sm"
                               onclick="return confirm('Are you sure you want to delete category: <?= htmlspecialchars($row['name']) ?>?');">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align: center;">No categories found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="form-container" id="editForm" style="display:none; margin-top: 20px;">
        <h3>Update Category</h3>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="name" id="edit_name" placeholder="Update Category Name" required>
            <button type="submit" name="update_category" class="btn-primary-action">Update</button>
            <button type="button" class="btn-primary-action" style="background: #95a5a6;" onclick="cancelEdit()">Cancel</button>
        </form>
    </div>

</div>

<script>
    function showEditModal(id, name) {
        document.getElementById("edit_id").value = id;
        document.getElementById("edit_name").value = name;
        document.getElementById("editForm").style.display = "flex"; // Use flex to match form-container style
        // Scroll to the form for better UX
        document.getElementById("editForm").scrollIntoView({ behavior: 'smooth' });
    }
    function cancelEdit() {
        document.getElementById("editForm").style.display = "none";
    }
</script>


<?php 
// --- TEMPLATING: END DASHBOARD LAYOUT ---
$conn->close();
include 'includes/footer.php'; 
?>