<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "Tác Giả - GTSCHUNDER";
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

// --- CẤU HÌNH PHÂN TRANG ---
$limit = 12; // Số tác giả mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Lấy tổng số tác giả
$stmtTotalCount = $pdo->query("SELECT COUNT(*) FROM authors WHERE IsDeleted = 0");
$totalAuthors = $stmtTotalCount->fetchColumn();
$totalPages = ceil($totalAuthors / $limit);

// Đảm bảo page hợp lệ
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}

// Lấy danh sách tác giả với số lượng truyện của họ
$sql = "
    SELECT a.AuthorID, a.Name, a.Avatar, a.Description,
           COUNT(DISTINCT aa.ArticleID) as TotalArticles
    FROM authors a
    LEFT JOIN articles_authors aa ON a.AuthorID = aa.AuthorID
    LEFT JOIN articles art ON aa.ArticleID = art.ArticleID AND art.IsDeleted = 0
    WHERE a.IsDeleted = 0
    GROUP BY a.AuthorID, a.Name, a.Avatar, a.Description
    ORDER BY a.Name ASC
    LIMIT ? OFFSET ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$limit, $offset]);
$authors = $stmt->fetchAll();
?>

<div class="main-container">
    <main class="content">
        <!-- TIÊU ĐỀ TRANG -->
        <section class="section">
            <div class="section-header">
                <h1 class="section-title">Danh Sách Tác Giả</h1>
                <p class="section-subtitle">Khám phá các tác giả nổi tiếng và những tác phẩm của họ</p>
            </div>
        </section>

        <!-- DANH SÁCH TÁC GIÃ -->
        <section class="section">
            <?php if (count($authors) > 0): ?>
                <div class="authors-grid">
                    <?php foreach ($authors as $author): ?>
                        <div class="author-card">
                            <div class="author-card-header">
                                <div class="author-avatar">
                                    <?php if (!empty($author['Avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($author['Avatar']); ?>" 
                                             alt="<?php echo htmlspecialchars($author['Name']); ?>"
                                             onerror="this.src='<?php echo BASE_URL; ?>uploads/avatars/1767718133_695d3cf586583.png'">
                                    <?php else: ?>
                                        <img src="<?php echo BASE_URL; ?>uploads/avatars/1767718133_695d3cf586583.png" alt="<?php echo htmlspecialchars($author['Name']); ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="author-card-body">
                                <h3 class="author-name">
                                    <a href="<?php echo BASE_URL; ?>author/<?php echo $author['AuthorID']; ?>">
                                        <?php echo htmlspecialchars($author['Name']); ?>
                                    </a>
                                </h3>
                                
                                <?php if (!empty($author['Description'])): ?>
                                    <p class="author-description">
                                        <?php echo htmlspecialchars(substr($author['Description'], 0, 100)); ?>
                                        <?php if (strlen($author['Description']) > 100): ?>
                                            ...
                                        <?php endif; ?>
                                    </p>
                                <?php else: ?>
                                    <p class="author-description">Không có mô tả</p>
                                <?php endif; ?>
                                
                                <div class="author-stats">
                                    <span class="stat-item">
                                        <i class="fas fa-book"></i>
                                        <span class="stat-value"><?php echo $author['TotalArticles']; ?></span>
                                        <span class="stat-label">Truyện</span>
                                    </span>
                                </div>
                                
                                <a href="<?php echo BASE_URL; ?>author/<?php echo $author['AuthorID']; ?>" class="author-view-btn">
                                    Xem Các Tác Phẩm
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
                            <a href="authors.php?page=1" class="pagination-link pagination-first">
                                <i class="fas fa-chevron-left"></i> Đầu
                            </a>
                            <a href="authors.php?page=<?php echo $page - 1; ?>" class="pagination-link pagination-prev">
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
                                <a href="authors.php?page=1" class="pagination-link">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif;
                            endif;
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                                if ($i == $page): ?>
                                    <span class="pagination-link active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="authors.php?page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                                <?php endif;
                            endfor;
                            
                            if ($endPage < $totalPages):
                                if ($endPage < $totalPages - 1): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                                <a href="authors.php?page=<?php echo $totalPages; ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                            <?php endif; ?>
                        </div>

                        <!-- Nút Next -->
                        <?php if ($page < $totalPages): ?>
                            <a href="authors.php?page=<?php echo $page + 1; ?>" class="pagination-link pagination-next">
                                Tiếp <i class="fas fa-chevron-right"></i>
                            </a>
                            <a href="authors.php?page=<?php echo $totalPages; ?>" class="pagination-link pagination-last">
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
                    <p>Không có tác giả nào</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- SIDEBAR PHẢI -->
    <?php include 'includes/right_sidebar.php'; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
