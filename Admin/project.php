<?php
session_start();

// ✅ Security Check
if (!isset($_SESSION['admin_username'])) {
    header("Location: adminlogin.php");
    exit;
}

// ✅ Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "myprofile";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = "";
// Check for a message passed via URL after a redirect
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars(urldecode($_GET['msg']));
}

/* ===============================================
   1️⃣ FETCH CATEGORIES FOR DROPDOWN (and store for JS)
   =============================================== */
$allCategories = [];
$catResult = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
while($row = $catResult->fetch_assoc()) {
    $allCategories[] = $row;
}
// Reset the pointer for the ADD form display
$catResult->data_seek(0);


/* ===============================================
   2️⃣ ADD NEW PROJECT (with Transaction and Redirect)
   =============================================== */
if (isset($_POST['add_project'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $project_link = trim($_POST['project_link']);

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO project_tbl (title, description, category_id, project_link) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $title, $description, $category_id, $project_link);
        $stmt->execute();
        $project_id = $stmt->insert_id;

        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = "uploads/";
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $filename = time() . "_" . uniqid() . "." . $ext;
                    $target = $uploadDir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $target)) {
                        $conn->query("INSERT INTO project_images (project_id, image_path) VALUES ($project_id, '$target')");
                    }
                }
            }
        }
        $conn->commit();
        $msg = "✅ Project added successfully!";
        header("Location: Project.php?msg=" . urlencode($msg));
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "❌ Error adding project: " . $e->getMessage();
        header("Location: Project.php?msg=" . urlencode($msg));
        exit;
    }
}

/* ===============================================
   3️⃣ DELETE PROJECT (Transactional and Secure)
   =============================================== */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->begin_transaction();

    try {
        // Delete images from disk
        $img_res = $conn->query("SELECT image_path FROM project_images WHERE project_id=$id");
        while ($img = $img_res->fetch_assoc()) {
            if (file_exists($img['image_path'])) {
                unlink($img['image_path']);
            }
        }
        // Delete records
        $conn->query("DELETE FROM project_images WHERE project_id=$id");
        $conn->query("DELETE FROM project_tbl WHERE id=$id");

        $conn->commit();
        $msg = "🗑️ Project deleted successfully!";
        header("Location: Project.php?msg=" . urlencode($msg)); 
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $msg = "❌ Error deleting project: " . $e->getMessage();
        header("Location: Project.php?msg=" . urlencode($msg)); 
        exit;
    }
}

/* ===============================================
   4️⃣ UPDATE PROJECT (with Transaction and Redirect)
   =============================================== */
if (isset($_POST['update_project'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $project_link = trim($_POST['project_link']);

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE project_tbl SET title=?, description=?, category_id=?, project_link=? WHERE id=?");
        $stmt->bind_param("ssisi", $title, $description, $category_id, $project_link, $id);
        
        if ($stmt->execute()) {
            // Handle new image uploads (Existing images remain)
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = "uploads/";
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                        $filename = time() . "_" . uniqid() . "." . $ext;
                        $target = $uploadDir . $filename;

                        if (move_uploaded_file($tmp_name, $target)) {
                            $conn->query("INSERT INTO project_images (project_id, image_path) VALUES ($id, '$target')");
                        }
                    }
                }
            }
            $conn->commit();
            $msg = "✅ Project updated successfully!";
        } else {
            throw new Exception("Error updating project: " . $stmt->error);
        }
        header("Location: Project.php?msg=" . urlencode($msg));
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "❌ Error updating project: " . $e->getMessage();
        header("Location: Project.php?msg=" . urlencode($msg));
        exit;
    }
}

/* ===============================================
   5️⃣ FETCH ALL PROJECTS (CRITICALLY includes category ID)
   =============================================== */
$query = "SELECT p.id, p.title, p.description, p.project_link, c.id AS category_id, c.name AS category_name
          FROM project_tbl p
          JOIN categories c ON p.category_id = c.id
          ORDER BY p.id DESC";
$result = $conn->query($query);

include 'includes/header.php';
?>

<style>
    /* 🎨 Define CSS variables using your color palette */
    :root {
        --primary-dark: #03396c; /* Dark Blue */
        --primary-light: #005b96; /* Medium Blue */
        --secondary-light: #6497b1; /* Light Blue */
        --tertiary-light: #b3cde0; /* Very Light Blue */
        --action-color: #6F9F9C; /* Teal Green - 'enter one' button color */
        --action-hover: #5d8c89; /* Darker Teal */
        --delete-color: #e74c3c; /* Red */
        --karma-color: #f39c12; /* Karma/Secondary button color (e.g., Cancel) */
        --karma-hover: #e67e22; /* Darker Karma */
    }
    
    .content-area {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        margin-top: 30px;
    }
    h2 { color: var(--primary-dark); margin-bottom: 25px; }
    h3 { color: var(--primary-light); margin-top: 0; margin-bottom: 15px; font-weight: 500; }

    .msg {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-weight: 500;
        text-align: center;
        background: var(--tertiary-light);
        color: var(--primary-dark);
        border: 1px solid var(--secondary-light);
    }
    
    form {
        background: #f8f8f8;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 30px;
        border-left: 5px solid var(--action-color);
    }
    form input[type="text"], form textarea, form select, form input[type="file"] {
        width: 100%;
        padding: 12px;
        margin: 8px 0;
        border: 1px solid var(--tertiary-light);
        border-radius: 6px;
        box-sizing: border-box; 
    }

    /* Button Styling */
    .btn-action {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
    }
    
    /* Primary Button: Add/Update */
    form button[name$="_project"] {
        background: var(--action-color);
        color: white;
    }
    form button[name$="_project"]:hover {
        background: var(--action-hover);
        transform: translateY(-2px); /* Mouse point hover animation */
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    /* Secondary Button: Cancel */
    form button[type="button"] {
        background: var(--karma-color);
        color: white;
        margin-left: 10px;
    }
    form button[type="button"]:hover {
        background: var(--karma-hover);
        transform: translateY(-2px); /* Mouse point hover animation */
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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


    /* Table & Actions */
    .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .data-table th { background: var(--primary-dark); color: #fff; padding: 12px; }
    .data-table td { border-bottom: 1px solid var(--tertiary-light); padding: 10px; }
    
    .project-images img {
        height: 50px;
        width: 50px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 5px;
        transition: transform 0.2s;
    }
    .project-images img:hover { transform: scale(1.1); box-shadow: 0 2px 5px rgba(0,0,0,0.3); }

    .actions a {
        text-decoration: none;
        margin-right: 5px;
        padding: 8px 12px;
        font-size: 0.9em;
        display: inline-block;
        border-radius: 6px;
        transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
    }
    .actions a.edit {
        background: var(--primary-light);
        color: white;
    }
    .actions a.delete {
        background: var(--delete-color);
        color: white;
    }
    .actions a.edit:hover, .actions a.delete:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
</style>

<div class="content-area">
    <h2>Project Management</h2>
    <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <h3>Add New Project</h3>
        <input type="text" name="title" placeholder="Project Title" required>
        <textarea name="description" placeholder="Project Description" rows="4"></textarea>
        <select name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($allCategories as $cat): // Using the stored array ?>
                <option value="<?= $cat['id']; ?>"><?= htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="project_link" placeholder="Project Link (optional)">
        <input type="file" name="images[]" multiple accept="image/*">
        <button type="submit" name="add_project" class="btn-action">Add Project</button>
    </form>

    <hr style="border-top: 1px solid var(--tertiary-light); margin: 30px 0;">

    <div class="table-scroll">
        <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Images</th>
                <th>Project Link</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
                $images = $conn->query("SELECT image_path FROM project_images WHERE project_id={$row['id']}");
            ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td><?= htmlspecialchars($row['category_name']); ?></td>
                <td class="project-images">
                    <?php while ($img = $images->fetch_assoc()): ?>
                        <img src="<?= htmlspecialchars($img['image_path']); ?>" alt="Project Image">
                    <?php endwhile; ?>
                </td>
                <td><a href="<?= htmlspecialchars($row['project_link']); ?>" target="_blank">View</a></td>
                <td class="actions">
                    <a href="#" class="edit" onclick="editProject(<?= $row['id']; ?>, '<?= htmlspecialchars(addslashes($row['title']), ENT_QUOTES); ?>', '<?= htmlspecialchars(addslashes($row['description']), ENT_QUOTES); ?>', <?= $row['category_id']; ?>, '<?= htmlspecialchars($row['project_link']); ?>')">Edit</a>
                    <a href="?delete=<?= $row['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this project?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>

    <div id="editForm" style="display:none; margin-top:30px;">
        <h3>Update Project</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="title" id="edit_title" required>
            <textarea name="description" id="edit_description" rows="4"></textarea>
            <select name="category_id" id="edit_category_id" required></select>
            <input type="text" name="project_link" id="edit_project_link">
            <label>Add More Images (optional):</label>
            <input type="file" name="images[]" multiple accept="image/*">
            <button type="submit" name="update_project" class="btn-action">Update Project</button>
            <button type="button" onclick="cancelEdit()" class="btn-action">Cancel</button>
        </form>
    </div>
</div>

<script>
// CRITICAL FIX: Make the category list available to JavaScript
const allCategories = <?= json_encode($allCategories); ?>;

function editProject(id, title, description, category_id, link) {
    // 1. Show the form and populate text fields
    document.getElementById("editForm").style.display = "block";
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_title").value = title;
    // Handle newlines correctly in the textarea
    document.getElementById("edit_description").value = description.replace(/\\n/g, '\n'); 
    document.getElementById("edit_project_link").value = link;

    // 2. Populate and select Category Dropdown
    const select = document.getElementById("edit_category_id");
    select.innerHTML = ''; // Clear previous options
    
    // Add default/placeholder option
    const defaultOption = document.createElement('option');
    defaultOption.value = "";
    defaultOption.textContent = "-- Select Category --";
    select.appendChild(defaultOption);

    allCategories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.name;
        
        // Select the option whose ID matches the project's category_id
        if (parseInt(cat.id) === category_id) {
            option.selected = true; 
        }
        select.appendChild(option);
    });
    
    // 3. Scroll to the form
    window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'});
}

function cancelEdit() {
    document.getElementById("editForm").style.display = "none";
}
</script>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>