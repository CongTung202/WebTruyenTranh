<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// 1. Lấy danh sách thể loại
$stmtGenres = $pdo->query("SELECT * FROM genres ORDER BY Name ASC");
$allGenres = $stmtGenres->fetchAll();

// 2. Xử lý Lọc
$currentGenreId = $_GET['genre_id'] ?? 0;
$pageTitle = "Thể loại";

if ($currentGenreId > 0) {
    foreach($allGenres as $g) {
        if ($g['GenreID'] == $currentGenreId) {
            $pageTitle = $g['Name'];
            break;
        }
    }
    $sql = "SELECT a.* FROM articles a
            JOIN articles_genres ag ON a.ArticleID = ag.ArticleID
            WHERE ag.GenreID = ? AND a.IsDeleted = 0
            ORDER BY a.UpdatedAt DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentGenreId]);
} else {
    $pageTitle = "Tất cả thể loại";
    $stmt = $pdo->query("SELECT * FROM articles WHERE IsDeleted = 0 ORDER BY UpdatedAt DESC");
}

$articles = $stmt->fetchAll();

// --- LOGIC AJAX ---
if (isset($_GET['ajax'])) {
    renderGenreContent($pageTitle, $articles);
    exit;
}

require_once 'includes/header.php';
?>

<style>
    /* CSS Nút Thể loại */
    .genre-nav { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color); }
    .btn-genre {
        padding: 6px 16px; border-radius: 20px; border: 1px solid var(--border-color);
        background-color: var(--bg-element); color: var(--text-muted); font-size: 13px; font-weight: 500;
        transition: all 0.2s; text-decoration: none; cursor: pointer;
    }
    .btn-genre:hover { border-color: var(--primary-theme); color: var(--text-main); }
    .btn-genre.active { background-color: var(--primary-theme); color: #fff; border-color: var(--primary-theme); font-weight: bold; }
    
    .loading-overlay { opacity: 0.5; pointer-events: none; }
</style>

<div class="main-container">
    <main class="content">
        
        <section class="section">
            <div class="section__header">
                <h3>Tìm theo thể loại</h3>
            </div>

            <div class="genre-nav">
                <a href="genres.php" class="btn-genre ajax-genre <?= $currentGenreId == 0 ? 'active' : '' ?>">
                    Tất cả
                </a>
                
                <?php foreach($allGenres as $g): ?>
                    <a href="genres.php?genre_id=<?= $g['GenreID'] ?>" 
                       class="btn-genre ajax-genre <?= $currentGenreId == $g['GenreID'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($g['Name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div id="ajax-genre-content">
                <?php renderGenreContent($pageTitle, $articles); ?>
            </div>
        </section>

    </main>

    <aside class="sidebar">
        <?php include 'includes/right_sidebar.php'; ?>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const genreBtns = document.querySelectorAll('.ajax-genre');
    const genreArea = document.getElementById('ajax-genre-content');

    genreBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            // Active UI
            genreBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Loading
            genreArea.classList.add('loading-overlay');

            // Fetch
            const url = this.getAttribute('href');
            window.history.pushState(null, '', url);

            fetch(url + (url.includes('?') ? '&' : '?') + 'ajax=1')
                .then(res => res.text())
                .then(html => {
                    genreArea.innerHTML = html;
                    genreArea.classList.remove('loading-overlay');
                })
                .catch(err => console.error(err));
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

<?php
// --- HÀM RENDER ---
function renderGenreContent($title, $list) {
?>
    <div class="section__header">
        <h3><?= htmlspecialchars($title) ?></h3>
        <span style="font-size: 12px; color: var(--text-muted); margin-left: auto;">
            Tìm thấy <?= count($list) ?> truyện
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
                        <div style="width:100%; height:100%; background:#333; display:flex; align-items:center; justify-content:center; color:#777;">No Image</div>
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
            <i class="far fa-sad-tear" style="font-size: 40px; margin-bottom: 15px;"></i>
            <p>Chưa có truyện nào thuộc thể loại này.</p>
            <a href="genres.php" style="color: var(--primary-theme); font-weight: bold;">Xem tất cả thể loại</a>
        </div>
    <?php endif; ?>
<?php
}
?>