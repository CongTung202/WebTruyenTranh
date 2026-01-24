<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "Chi Tiết Tác Giả - GTSCHUNDER";
require_once 'includes/header.php';

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

// Lấy AuthorID từ URL
$authorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($authorId <= 0) {
    header('Location: authors.php');
    exit;
}

// Lấy thông tin tác giả
$stmtAuthor = $pdo->prepare("SELECT * FROM authors WHERE AuthorID = ? AND IsDeleted = 0");
$stmtAuthor->execute([$authorId]);
$author = $stmtAuthor->fetch();

if (!$author) {
    header('Location: authors.php');
    exit;
}

$pageTitle = htmlspecialchars($author['Name']) . " - GTSCHUNDER";

// --- CẤU HÌNH PHÂN TRANG ---
$limit = 12; // Số truyện mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Đếm tổng số truyện của tác giả
$stmtCount = $pdo->prepare("
    SELECT COUNT(*) FROM articles a
    JOIN articles_authors aa ON a.ArticleID = aa.ArticleID
    WHERE aa.AuthorID = ? AND a.IsDeleted = 0
");
$stmtCount->execute([$authorId]);
$totalArticles = $stmtCount->fetchColumn();
$totalPages = ceil($totalArticles / $limit);

// Đảm bảo page hợp lệ
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}

// Lấy danh sách truyện của tác giả
$sql = '
    SELECT a.*, 
           (SELECT c.`Index` FROM chapters c WHERE c.ArticleID = a.ArticleID AND c.IsDeleted = 0 ORDER BY c.CreatedAt DESC LIMIT 1) as LatestChapterIndex,
           (SELECT c.Title FROM chapters c WHERE c.ArticleID = a.ArticleID AND c.IsDeleted = 0 ORDER BY c.CreatedAt DESC LIMIT 1) as LatestChapterTitle,
           (SELECT c.CreatedAt FROM chapters c WHERE c.ArticleID = a.ArticleID AND c.IsDeleted = 0 ORDER BY c.CreatedAt DESC LIMIT 1) as LatestChapterDate,
           GROUP_CONCAT(DISTINCT CONCAT(g.GenreID, ":", g.Name) SEPARATOR ", ") as GenreData
    FROM articles a
    JOIN articles_authors aa ON a.ArticleID = aa.ArticleID
    LEFT JOIN articles_genres ag ON a.ArticleID = ag.ArticleID
    LEFT JOIN genres g ON ag.GenreID = g.GenreID AND g.IsDeleted = 0
    WHERE aa.AuthorID = ? AND a.IsDeleted = 0
    GROUP BY a.ArticleID
    ORDER BY a.UpdatedAt DESC
    LIMIT ? OFFSET ?
';

$stmt = $pdo->prepare($sql);
$stmt->execute([$authorId, $limit, $offset]);
$articles = $stmt->fetchAll();
?>

<div class="main-container">
    <main class="content">
        <!-- THÔNG TIN TÁC GIÃ -->
        <section class="section author-info-section">
            <div class="author-info-container">
                <div class="author-info-header">
                    <div class="author-info-avatar">
                        <?php if (!empty($author['Avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($author['Avatar']); ?>" 
                                 alt="<?php echo htmlspecialchars($author['Name']); ?>"
                                 onerror="this.src='<?php echo BASE_URL; ?>uploads/avatars/1767718133_695d3cf586583.png'">
                        <?php else: ?>
                            <img src="<?php echo BASE_URL; ?>uploads/avatars/1767718133_695d3cf586583.png" alt="<?php echo htmlspecialchars($author['Name']); ?>">
                        <?php endif; ?>
                    </div>
                    
                    <div class="author-info-details">
                        <h1 class="author-info-name"><?php echo htmlspecialchars($author['Name']); ?></h1>
                        
                        <div class="author-info-stats">
                            <div class="stat-box">
                                <span class="stat-number"><?php echo $totalArticles; ?></span>
                                <span class="stat-label">Tác Phẩm</span>
                            </div>
                        </div>
                        
                        <?php if (!empty($author['Description'])): ?>
                            <div class="author-info-bio">
                                <h3>Về Tác Giả</h3>
                                <p><?php echo nl2br(htmlspecialchars($author['Description'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- TIÊU ĐỀ DANH SÁCH TRUYỆN -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Các Tác Phẩm Của <?php echo htmlspecialchars($author['Name']); ?></h2>
            </div>
        </section>

        <!-- DANH SÁCH TRUYỆN -->
        <section class="section">
            <?php if (count($articles) > 0): ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $article): ?>
                        <div class="article-card">
                            <div class="article-cover">
                                <a href="detail.php?id=<?php echo $article['ArticleID']; ?>">
                                    <img src="<?php echo !empty($article['CoverImage']) ? htmlspecialchars($article['CoverImage']) : 'default/default-cover.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($article['Title']); ?>"
                                         onerror="this.src='default/default-cover.png'">
                                </a>
                                
                                <?php if (!empty($article['LatestChapterIndex'])): ?>
                                    <div class="article-badge">
                                        <span class="badge badge-chapter">Ch. <?php echo htmlspecialchars($article['LatestChapterIndex']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="article-info">
                                <h3 class="article-title">
                                    <a href="detail.php?id=<?php echo $article['ArticleID']; ?>">
                                        <?php echo htmlspecialchars($article['Title']); ?>
                                    </a>
                                </h3>
                                
                                <?php if (!empty($article['GenreData'])): ?>
                                    <div class="article-genres">
                                        <?php 
                                        $genreItems = explode(', ', $article['GenreData']);
                                        foreach (array_slice($genreItems, 0, 2) as $genreItem):
                                            $parts = explode(':', $genreItem);
                                            $genreId = $parts[0] ?? '';
                                            $genreName = $parts[1] ?? '';
                                        ?>
                                            <a href="<?php echo BASE_URL; ?>genre/<?php echo $genreId; ?>" class="genre-tag" onclick="event.stopPropagation();"><?php echo htmlspecialchars($genreName); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="article-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-eye"></i> <?php echo number_format($article['ViewCount']); ?>
                                    </span>
                                    <?php if (!empty($article['LatestChapterTitle'])): ?>
                                        <span class="meta-item">
                                            <i class="fas fa-calendar"></i> <?php echo getRelativeTime($article['LatestChapterDate']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="detail.php?id=<?php echo $article['ArticleID']; ?>" class="article-btn">
                                    Xem Chi Tiết
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- PHÂN TRANG -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <!-- Nút Previous -->
                        <?php if ($page > 1): ?>
                            <a href="author-detail.php?id=<?php echo $authorId; ?>&page=1" class="pagination-link pagination-first">
                                <i class="fas fa-chevron-left"></i> Đầu
                            </a>
                            <a href="author-detail.php?id=<?php echo $authorId; ?>&page=<?php echo $page - 1; ?>" class="pagination-link pagination-prev">
                                <i class="fas fa-chevron-left"></i> Trước
                            </a>
                        <?php else: ?>
                            <span class="pagination-link disabled"><i class="fas fa-chevron-left"></i> Đầu</span>
                            <span class="pagination-link disabled"><i class="fas fa-chevron-left"></i> Trước</span>
                        <?php endif; ?>

                        <!-- Các số trang -->
                        <div class="pagination-numbers">
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            if ($startPage > 1): ?>
                                <a href="author-detail.php?id=<?php echo $authorId; ?>&page=1" class="pagination-link">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif;
                            endif;
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                                if ($i == $page): ?>
                                    <span class="pagination-link active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="author-detail.php?id=<?php echo $authorId; ?>&page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                                <?php endif;
                            endfor;
                            
                            if ($endPage < $totalPages):
                                if ($endPage < $totalPages - 1): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                                <a href="author-detail.php?id=<?php echo $authorId; ?>&page=<?php echo $totalPages; ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                            <?php endif; ?>
                        </div>

                        <!-- Nút Next -->
                        <?php if ($page < $totalPages): ?>
                            <a href="author-detail.php?id=<?php echo $authorId; ?>&page=<?php echo $page + 1; ?>" class="pagination-link pagination-next">
                                Tiếp <i class="fas fa-chevron-right"></i>
                            </a>
                            <a href="author-detail.php?id=<?php echo $authorId; ?>&page=<?php echo $totalPages; ?>" class="pagination-link pagination-last">
                                Cuối <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="pagination-link disabled">Tiếp <i class="fas fa-chevron-right"></i></span>
                            <span class="pagination-link disabled">Cuối <i class="fas fa-chevron-right"></i></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-content">
                    <p>Tác giả này chưa có tác phẩm nào</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- SIDEBAR PHẢI -->
    <?php include 'includes/right_sidebar.php'; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
