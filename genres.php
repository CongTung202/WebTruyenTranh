<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Hàm tính thời gian tương đối
function getRelativeTime($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return $diff . ' giây trước';
    else if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    else if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    else if ($diff < 604800) return floor($diff / 86400) . ' ngày trước';
    else if ($diff < 2592000) return floor($diff / 604800) . ' tuần trước';
    else return floor($diff / 2592000) . ' tháng trước';
}

// --- CẤU HÌNH PHÂN TRANG ---
$limit = 12; // Số truyện mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 1. Lấy danh sách thể loại
$stmtGenres = $pdo->query("SELECT * FROM genres ORDER BY Name ASC");
$allGenres = $stmtGenres->fetchAll();

// 2. Xử lý Lọc
$currentGenreId = $_GET['genre_id'] ?? 0;
$pageTitle = "Thể loại";
$baseUrl = "genres.php?genre_id=$currentGenreId";

if ($currentGenreId > 0) {
    foreach($allGenres as $g) {
        if ($g['GenreID'] == $currentGenreId) {
            $pageTitle = $g['Name'];
            break;
        }
    }
    // Đếm tổng
    $sqlCount = "SELECT COUNT(*) FROM articles a
                 JOIN articles_genres ag ON a.ArticleID = ag.ArticleID
                 WHERE ag.GenreID = ? AND a.IsDeleted = 0";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute([$currentGenreId]);
    $totalArticles = $stmtCount->fetchColumn();

    // Lấy dữ liệu
    $sql = "SELECT a.*, 
                   GROUP_CONCAT(DISTINCT auth.Name SEPARATOR ', ') as Authors,
                   GROUP_CONCAT(DISTINCT CONCAT(g.GenreID, ':', g.Name) SEPARATOR ', ') as GenreData,
                   (SELECT c.`Index` FROM chapters c WHERE c.ArticleID = a.ArticleID AND c.IsDeleted = 0 ORDER BY c.CreatedAt DESC LIMIT 1) as LatestChapterIndex,
                   (SELECT c.CreatedAt FROM chapters c WHERE c.ArticleID = a.ArticleID AND c.IsDeleted = 0 ORDER BY c.CreatedAt DESC LIMIT 1) as LatestChapterDate
            FROM articles a
            LEFT JOIN articles_authors aa ON a.ArticleID = aa.ArticleID
            LEFT JOIN authors auth ON aa.AuthorID = auth.AuthorID
            JOIN articles_genres ag ON a.ArticleID = ag.ArticleID
            LEFT JOIN genres g ON ag.GenreID = g.GenreID
            WHERE ag.GenreID = ? AND a.IsDeleted = 0
            GROUP BY a.ArticleID
            ORDER BY a.UpdatedAt DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentGenreId]);
} else {
    $pageTitle = "Tất cả thể loại";
    // Đếm tổng
    $totalArticles = $pdo->query("SELECT COUNT(*) FROM articles WHERE IsDeleted = 0")->fetchColumn();
    // Lấy dữ liệu
    $stmt = $pdo->query("
        SELECT a.*, 
               GROUP_CONCAT(DISTINCT auth.Name SEPARATOR ', ') as Authors,
               GROUP_CONCAT(DISTINCT CONCAT(g.GenreID, ':', g.Name) SEPARATOR ', ') as GenreData,
               (SELECT c.`Index` FROM chapters c WHERE c.ArticleID = a.ArticleID AND c.IsDeleted = 0 ORDER BY c.CreatedAt DESC LIMIT 1) as LatestChapterIndex,
               (SELECT c.CreatedAt FROM chapters c WHERE c.ArticleID = a.ArticleID AND c.IsDeleted = 0 ORDER BY c.CreatedAt DESC LIMIT 1) as LatestChapterDate
        FROM articles a
        LEFT JOIN articles_authors aa ON a.ArticleID = aa.ArticleID
        LEFT JOIN authors auth ON aa.AuthorID = auth.AuthorID
        LEFT JOIN articles_genres ag ON a.ArticleID = ag.ArticleID
        LEFT JOIN genres g ON ag.GenreID = g.GenreID
        WHERE a.IsDeleted = 0 
        GROUP BY a.ArticleID
        ORDER BY a.UpdatedAt DESC LIMIT $limit OFFSET $offset
    ");
}

$articles = $stmt->fetchAll();
$totalPages = ceil($totalArticles / $limit);

// --- LOGIC AJAX ---
if (isset($_GET['ajax'])) {
    renderGenreContent($pageTitle, $articles, $page, $totalPages, $baseUrl);
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
    
    /* CSS Phân trang (Đã có style trong file global hoặc copy từ types.php) */
    .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 40px; }
    .page-link {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 35px; height: 35px; padding: 0 10px;
        border: 1px solid var(--border-color); border-radius: 4px;
        color: var(--text-muted); text-decoration: none; font-size: 13px;
        transition: 0.2s; background: var(--bg-element);
    }
    .page-link:hover { border-color: var(--text-main); color: var(--text-main); }
    .page-link.active { background: var(--primary-theme); color: #fff; border-color: var(--primary-theme); font-weight: bold; }

    .loading-overlay { opacity: 0.5; pointer-events: none; }
</style>

<div class="main-container">
    <main class="content">
        
        <section class="section">
            <div class="section__header">
                <h3>Tìm theo thể loại</h3>
            </div>

            <div class="genre-nav">
                <a href="<?= BASE_URL ?>genre/0" class="btn-genre ajax-genre <?= $currentGenreId == 0 ? 'active' : '' ?>" data-url="<?= BASE_URL ?>genres?genre_id=0">
                    Tất cả
                </a>
                
                <?php foreach($allGenres as $g): ?>
                    <a href="<?= BASE_URL ?>genre/<?= $g['GenreID'] ?>" 
                       class="btn-genre ajax-genre <?= $currentGenreId == $g['GenreID'] ? 'active' : '' ?>"
                       data-url="<?= BASE_URL ?>genres?genre_id=<?= $g['GenreID'] ?>">
                        <?= htmlspecialchars($g['Name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div id="ajax-genre-content">
                <?php renderGenreContent($pageTitle, $articles, $page, $totalPages, $baseUrl); ?>
            </div>
        </section>

    </main>

    <aside class="sidebar">
        <?php include 'includes/right_sidebar.php'; ?>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const genreArea = document.getElementById('ajax-genre-content');

    document.body.addEventListener('click', function(e) {
        // Bắt sự kiện click vào nút Thể loại HOẶC nút Phân trang
        const target = e.target.closest('.ajax-genre') || e.target.closest('.page-link');

        if (target && !target.classList.contains('active')) {
            e.preventDefault();

            if (target.classList.contains('ajax-genre')) {
                document.querySelectorAll('.ajax-genre').forEach(b => b.classList.remove('active'));
                target.classList.add('active');
            }

            const url = target.getAttribute('href') || target.dataset.url;
            if (!url) return;

            // Loading
            genreArea.classList.add('loading-overlay');
            window.history.pushState(null, '', url);

            fetch(url + (url.includes('?') ? '&' : '?') + 'ajax=1')
                .then(res => res.text())
                .then(html => {
                    genreArea.innerHTML = html;
                    genreArea.classList.remove('loading-overlay');
                    window.scrollTo({ top: 100, behavior: 'smooth' });
                })
                .catch(err => console.error(err));
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

<?php
function renderGenreContent($title, $list, $page, $totalPages, $baseUrl) {
?>
    <div class="section__header">
        <h3><?= htmlspecialchars($title) ?></h3>
        <span style="font-size: 12px; color: var(--text-muted); margin-left: auto;">
            Trang <?= $page ?> / <?= $totalPages > 0 ? $totalPages : 1 ?>
        </span>
    </div>

    <?php if (count($list) > 0): ?>
        <div class="card-list">
            <?php foreach($list as $art): ?>
            <article class="card" onclick="window.location.href='<?= BASE_URL ?>truyen/<?= $art['ArticleID'] ?>'">
                <div class="card__thumb">
                    <?php if($art['CoverImage']): ?>
                        <img src="<?= getImageUrl($art['CoverImage']) ?>" alt="<?= htmlspecialchars($art['Title']) ?>">
                    <?php else: ?>
                        <div style="width:100%; height:100%; background:#333; display:flex; align-items:center; justify-content:center; color:#777;">No Image</div>
                    <?php endif; ?>
                </div>
                <h4 class="card__title"><?= htmlspecialchars($art['Title']) ?></h4>
                <div class="card__genres" style="display: flex; gap: 4px; margin-bottom: 8px; flex-wrap: wrap;">
                    <?php 
                    if (!empty($art['GenreData'])) {
                        $genreItems = explode(', ', $art['GenreData']);
                        foreach (array_slice($genreItems, 0, 2) as $genreItem) {
                            $parts = explode(':', $genreItem);
                            $genreId = $parts[0] ?? '';
                            $genreName = $parts[1] ?? '';
                            echo '<a href="' . BASE_URL . 'genre/' . $genreId . '/" style="display: inline-block; background-color: var(--bg-hover); color: var(--text-muted); padding: 2px 6px; border-radius: 3px; font-size: 10px; border: 1px solid var(--border-color); text-decoration: none; transition: all 0.2s;" onmouseover="this.style.color=\'var(--primary-theme)\'; this.style.borderColor=\'var(--primary-theme)\';" onmouseout="this.style.color=\'var(--text-muted)\'; this.style.borderColor=\'var(--border-color)\';" onclick="event.stopPropagation();">' . htmlspecialchars(trim($genreName)) . '</a>';
                        }
                    }
                    ?>
                </div>
                <p class="card__chapter" style="font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                    <?php 
                    if (!empty($art['LatestChapterDate'])) {
                        $chapterIndex = !empty($art['LatestChapterIndex']) ? $art['LatestChapterIndex'] : '?';
                        $relativeTime = getRelativeTime($art['LatestChapterDate']);
                        echo '<i class="fas fa-book-open"></i> Chương ' . $chapterIndex . ' - ' . $relativeTime;
                    } else {
                        echo '<i class="fas fa-book-open"></i> Chưa có chương';
                    }
                    ?>
                </p>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= $baseUrl ?>&page=<?= $page - 1 ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>

            <?php 
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++): 
            ?>
                <a href="<?= $baseUrl ?>&page=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= $baseUrl ?>&page=<?= $page + 1 ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div style="text-align: center; padding: 50px; color: var(--text-muted);">
            <i class="far fa-sad-tear" style="font-size: 40px; margin-bottom: 15px;"></i>
            <p>Chưa có truyện nào thuộc thể loại này.</p>
            <a href="<?= BASE_URL ?>genres" style="color: var(--primary-theme); font-weight: bold;">Xem tất cả thể loại</a>
        </div>
    <?php endif; ?>
<?php
}
?>