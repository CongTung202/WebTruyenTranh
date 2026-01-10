<?php
require_once 'includes/db.php';
require_once 'includes/functions.php'; // Để dùng getImageUrl

// 1. Lấy danh sách Categories
$allCats = $pdo->query("SELECT * FROM categories ORDER BY Name ASC")->fetchAll();

// 2. Xử lý Lọc
$currentCatId = $_GET['cat_id'] ?? 0;
$pageTitle = "Phân loại truyện";

if ($currentCatId > 0) {
    foreach($allCats as $c) {
        if ($c['CategoryID'] == $currentCatId) {
            $pageTitle = $c['Name'];
            break;
        }
    }
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE CategoryID = ? AND IsDeleted = 0 ORDER BY UpdatedAt DESC");
    $stmt->execute([$currentCatId]);
} else {
    $pageTitle = "Tất cả phân loại";
    $stmt = $pdo->query("SELECT * FROM articles WHERE IsDeleted = 0 ORDER BY UpdatedAt DESC");
}

$articles = $stmt->fetchAll();

// --- [LOGIC AJAX] ---
// Nếu có tham số ajax=1, chỉ in ra nội dung thẻ <section> rồi dừng
if (isset($_GET['ajax'])) {
    renderContent($pageTitle, $articles); // Gọi hàm hiển thị bên dưới
    exit; // Dừng ngay, không load header/footer
}

// Nếu không phải AJAX, load giao diện đầy đủ
require_once 'includes/header.php';
?>

<style>
    /* CSS Tab Navigation */
    .type-nav { 
        display: flex; justify-content: center; gap: 20px; 
        margin-bottom: 40px; border-bottom: 2px solid var(--border-color); 
    }
    .nav-type-item {
        padding: 15px 10px; font-weight: bold; color: var(--text-muted); font-size: 15px;
        border-bottom: 2px solid transparent; margin-bottom: -2px; transition: 0.2s;
        text-decoration: none; text-transform: uppercase; cursor: pointer;
    }
    .nav-type-item:hover { color: var(--text-main); }
    .nav-type-item.active { color: var(--primary-theme); border-bottom-color: var(--primary-theme); }
    
    /* Loading Effect */
    .loading-overlay { opacity: 0.5; pointer-events: none; transition: 0.2s; }
</style>

<div class="main-container">
    <main class="content">
        
        <div class="type-nav">
            <a href="types.php" class="nav-type-item ajax-tab <?= $currentCatId == 0 ? 'active' : '' ?>" data-id="0">
                Tất cả
            </a>
            <?php foreach($allCats as $c): ?>
                <a href="types.php?cat_id=<?= $c['CategoryID'] ?>" 
                   class="nav-type-item ajax-tab <?= $currentCatId == $c['CategoryID'] ? 'active' : '' ?>" 
                   data-id="<?= $c['CategoryID'] ?>">
                    <?= htmlspecialchars($c['Name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div id="ajax-content">
            <?php renderContent($pageTitle, $articles); ?>
        </div>

    </main>

    <aside class="sidebar">
        <?php include 'includes/right_sidebar.php'; ?>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.ajax-tab');
    const contentArea = document.getElementById('ajax-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault(); // Chặn load trang

            // 1. Xử lý giao diện Tab Active
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // 2. Hiệu ứng loading
            contentArea.classList.add('loading-overlay');

            // 3. Lấy URL cần gọi
            const url = this.getAttribute('href');
            
            // 4. Cập nhật URL trên thanh địa chỉ (để F5 vẫn đúng trang)
            window.history.pushState(null, '', url);

            // 5. Gọi AJAX
            fetch(url + (url.includes('?') ? '&' : '?') + 'ajax=1')
                .then(response => response.text())
                .then(html => {
                    contentArea.innerHTML = html; // Đắp HTML mới vào
                    contentArea.classList.remove('loading-overlay'); // Tắt loading
                })
                .catch(err => {
                    console.error('Lỗi:', err);
                    contentArea.classList.remove('loading-overlay');
                });
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

<?php
// --- HÀM RENDER NỘI DUNG (Dùng chung cho cả Load thường và AJAX) ---
function renderContent($title, $list) {
?>
    <section class="section">
        <div class="section__header">
            <h3><?= htmlspecialchars($title) ?></h3>
            <span style="font-size: 12px; color: var(--text-muted); margin-left: auto;">
                <?= count($list) ?> kết quả
            </span>
        </div>

        <?php if (count($list) > 0): ?>
            <div class="card-list">
                <?php foreach($list as $art): ?>
                <article class="card" onclick="window.location.href='detail.php?id=<?= $art['ArticleID'] ?>'">
                    <div class="card__thumb">
                        <?php if($art['CoverImage']): ?>
                            <img src="<?= getImageUrl($art['CoverImage']) ?>" alt="<?= htmlspecialchars($art['Title']) ?>">
                        <?php else: ?>
                            <div style="width:100%; height:100%; background:#333; display:flex; align-items:center; justify-content:center; color:#777;">No Img</div>
                        <?php endif; ?>
                    </div>
                    <h4 class="card__title"><?= htmlspecialchars($art['Title']) ?></h4>
                    <p class="card__author" style="font-size: 11px; color: var(--text-muted);">
                        <i class="fas fa-eye me-1"></i> <?= number_format($art['ViewCount']) ?>
                    </p>
                </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; color: var(--text-muted);">
                <i class="far fa-folder-open" style="font-size: 40px; margin-bottom: 15px;"></i>
                <p>Chưa có truyện nào thuộc phân loại này.</p>
            </div>
        <?php endif; ?>
    </section>
<?php
}
?>