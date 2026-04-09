<?php
session_start();
// Security check
if (!isset($_SESSION['admin_username'])) {
    header("Location: adminlogin.php");
    exit;
}

include 'db_connect.php';

// --- 1. FETCH TOTAL COUNTS ---
// (Your existing database logic remains here)
$category_count = 0;
// ... (rest of count logic) ...
$cat_result = $conn->query("SELECT COUNT(*) AS total_categories FROM categories");
if ($cat_result) { $category_count = $cat_result->fetch_assoc()['total_categories']; }
$project_count = 0;
$proj_result = $conn->query("SELECT COUNT(*) AS total_projects FROM project_tbl");
if ($proj_result) { $project_count = $proj_result->fetch_assoc()['total_projects']; }

// --- 2. FETCH PROJECT DATA ---
$projects = [];
$project_data = $conn->query("SELECT id, title, category_id, created_at FROM project_tbl ORDER BY created_at DESC LIMIT 5"); 

if ($project_data->num_rows > 0) {
    while ($row = $project_data->fetch_assoc()) {
        $projects[] = $row;
    }
}

// CLOSE DB CONNECTION
$conn->close();

// --- INCLUDE TEMPLATE FILES ---
include 'includes/header.php'; 
?>

<style>
    /* Scrollable Project Table (4 rows visible) */
.table-scroll {
    max-height: 550px; /* Shows ~4 projects */
    overflow-y: auto;
    border-radius: 8px;
}

/* Keep table header fixed while scrolling */
.table-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 2;
}

</style>

<div class="dashboard-content">

    <div class="info-cards">
        <div class="card">
            <i class="fas fa-tags icon"></i>
            <h3>Total Categories</h3>
            <div class="count"><?php echo $category_count; ?></div>
        </div>
        <div class="card">
            <i class="fas fa-project-diagram icon"></i>
            <h3>Total Projects</h3>
            <div class="count"><?php echo $project_count; ?></div>
        </div>
    </div>

    <div class="project-table-container">
        <h2>My Projects</h2>
        <div class="table-scroll">
            <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category ID</th>
                    <th>Date Added</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="5" style="text-align: center;">No projects found in the database.</td></tr>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($project['id']); ?></td>
                            <td><?php echo htmlspecialchars($project['title']); ?></td>
                            <td><?php echo htmlspecialchars($project['category_id']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($project['created_at'])); ?></td>
                            <td><a href="edit_project.php?id=<?php echo $project['id']; ?>" style="color: var(--action-color); text-decoration: none;"><i class="fas fa-edit"></i> Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
        <p style="text-align: right; margin-top: 15px; font-size: 0.9em;"><a href="project.php" style="color: var(--primary-dark);">View All Projects &raquo;</a></p>
    </div>

</div>
<?php include 'includes/footer.php'; ?>