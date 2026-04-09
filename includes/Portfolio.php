<?php
// ================= DATABASE CONNECTION =================
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "myprofile";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ================= PATH CONFIGURATION =================
$baseUrl   = "/portfolio.TSR";
$uploadUrl = $baseUrl . "/Admin/uploads/";

// ================= FETCH CATEGORIES =================
$categories = [];
$catResult = $conn->query("SELECT * FROM categories ORDER BY name ASC");
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}

// ================= FETCH PROJECTS =================
$projects = [];
$projResult = $conn->query("
    SELECT p.*, c.name AS category_name 
    FROM project_tbl p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
while ($row = $projResult->fetch_assoc()) {
    $projects[] = $row;
}

// ================= FETCH PROJECT IMAGES =================
$projectImages = [];
$imgResult = $conn->query("SELECT * FROM project_images ORDER BY project_id, id ASC");
while ($row = $imgResult->fetch_assoc()) {
    $imgPath = str_replace('uploads/', '', $row['image_path']);
    $projectImages[$row['project_id']][] = $imgPath;
}
?>

<!-- ================= PORTFOLIO SECTION ================= -->
<section class="portfolio" id="portfolio">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Portfolio</span>
            <h2>My Recent Work</h2>
            <p>Browse through a selection of my recent projects.</p>
        </div>

        <!-- ================= FILTERS ================= -->
        <div class="portfolio-filter">
            <button class="filter-btn active" data-filter="all">All Projects</button>
            <?php foreach ($categories as $cat): ?>
                <button class="filter-btn" data-filter="<?= htmlspecialchars($cat['name']) ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- ================= PORTFOLIO GRID ================= -->
        <div class="portfolio-grid">
            <?php
            $count = 0;
            foreach ($projects as $proj):
                $count++;

                $mainImage = '';
                if (!empty($projectImages[$proj['id']][0])) {
                    $mainImage = $uploadUrl . $projectImages[$proj['id']][0];
                }
            ?>
                <div class="portfolio-item"
                     data-category="<?= htmlspecialchars($proj['category_name']) ?>"
                     data-index="<?= $count ?>">
                    <div class="portfolio-image">
                        <img src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($proj['title']) ?>">
                        <div class="portfolio-overlay">
                            <a href="#" class="portfolio-link" data-target="p-<?= $proj['id'] ?>">View Details</a>
                        </div>
                    </div>
                    <div class="portfolio-content">
                        <h3><?= htmlspecialchars($proj['title']) ?></h3>
                        <span class="portfolio-category"><?= htmlspecialchars($proj['category_name']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ================= SEE MORE BUTTON ================= -->
        <?php if (count($projects) > 6): ?>
            <div class="see-more-wrapper" style="text-align:center;margin-top:30px;">
                <button id="seeMoreBtn" class="btn btn-primary">See More</button>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ================= PROJECT PREVIEW MODAL ================= -->
<div class="products-preview">
    <?php foreach ($projects as $proj): ?>
        <div class="preview" data-target="p-<?= $proj['id'] ?>">
            <i class="fas fa-times"></i>

            <?php
            if (!empty($projectImages[$proj['id']])) {
                foreach ($projectImages[$proj['id']] as $img) {
                    echo '<img src="' . htmlspecialchars($uploadUrl . $img) . '" alt="' . htmlspecialchars($proj['title']) . '"><br>';
                }
            }
            ?>

            <h3><?= htmlspecialchars($proj['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($proj['description'])) ?></p>

            <?php if (!empty($proj['project_link'])): ?>
                <a href="<?= htmlspecialchars($proj['project_link']) ?>" target="_blank">View Project</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    const portfolioItems = document.querySelectorAll(".portfolio-item");
    const filterBtns = document.querySelectorAll(".filter-btn");
    const seeMoreBtn = document.getElementById("seeMoreBtn");

    // ===== SHOW ONLY FIRST 6 INITIALLY =====
    portfolioItems.forEach(item => {
        if (item.dataset.index > 6) {
            item.style.display = "none";
        }
    });

    // ===== SEE MORE BUTTON =====
    if (seeMoreBtn) {
        seeMoreBtn.addEventListener("click", () => {
            portfolioItems.forEach(item => item.style.display = "block");
            seeMoreBtn.style.display = "none";
        });
    }

    // ===== FILTER =====
    filterBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            const filter = btn.dataset.filter;
            let visible = 0;

            filterBtns.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            portfolioItems.forEach(item => {
                if (filter === "all" || item.dataset.category === filter) {
                    visible++;
                    item.style.display = visible <= 6 ? "block" : "none";
                } else {
                    item.style.display = "none";
                }
            });

            if (seeMoreBtn) {
                seeMoreBtn.style.display = visible > 6 ? "inline-block" : "none";
            }
        });
    });

    // ===== MODAL OPEN =====
    document.querySelectorAll(".portfolio-link").forEach(link => {
        link.addEventListener("click", e => {
            e.preventDefault();
            document.querySelector(".products-preview").classList.add("active");
            document.querySelector(`[data-target="${link.dataset.target}"]`).classList.add("active");
        });
    });

    // ===== MODAL CLOSE =====
    document.querySelectorAll(".fa-times").forEach(btn => {
        btn.addEventListener("click", () => {
            btn.parentElement.classList.remove("active");
            document.querySelector(".products-preview").classList.remove("active");
        });
    });
});
</script>
