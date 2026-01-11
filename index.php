<?php
require_once 'includes/db.php';
$pageTitle = "Trang chủ - GTSCHUNDER";
require_once 'includes/header.php'; 

// 1. Lấy truyện mới cập nhật (kèm tác giả đầu tiên)
$stmtNew = $pdo->query("
    SELECT a.*, 
           GROUP_CONCAT(DISTINCT auth.Name SEPARATOR ', ') as Authors 
    FROM articles a 
    LEFT JOIN articles_authors aa ON a.ArticleID = aa.ArticleID 
    LEFT JOIN authors auth ON aa.AuthorID = auth.AuthorID 
    WHERE a.IsDeleted = 0 
    GROUP BY a.ArticleID
    ORDER BY a.UpdatedAt DESC 
    LIMIT 10
");
$articlesNew = $stmtNew->fetchAll();

// 2. Lấy truyện nhiều lượt xem (Top 5, kèm tác giả)
$stmtTop = $pdo->query("
    SELECT a.*, 
           GROUP_CONCAT(DISTINCT auth.Name SEPARATOR ', ') as Authors 
    FROM articles a 
    LEFT JOIN articles_authors aa ON a.ArticleID = aa.ArticleID 
    LEFT JOIN authors auth ON aa.AuthorID = auth.AuthorID 
    WHERE a.IsDeleted = 0 
    GROUP BY a.ArticleID
    ORDER BY a.ViewCount DESC 
    LIMIT 5
");
$articlesTop = $stmtTop->fetchAll();
?>
<div class="main-container">
<main class="content">
    
    <section class="section">
        <div class="section__header">
            <h3>Truyện mới đăng</h3>
            <a href="#" class="section__view-all">Xem tất cả ></a>
        </div>

        <div class="card-list">
            <?php foreach($articlesNew as $art): ?>
            <article class="card" onclick="window.location.href='<?= BASE_URL ?>truyen/<?= $art['ArticleID'] ?>'">
                <div class="card__thumb">
                    <?php if($art['CoverImage']): ?>
                        <img src="<?= getImageUrl($art['CoverImage']) ?>" alt="<?= htmlspecialchars($art['Title']) ?>">
                    <?php else: ?>
                        <div style="width:100%; height:100%; background:#333; display:flex; align-items:center; justify-content:center; color:#777;">No Image</div>
                    <?php endif; ?>
                    <span class="badge-up">UP</span>
                </div>
                <h4 class="card__title"><?= htmlspecialchars($art['Title']) ?></h4>
                <p class="card__author">
                    <?php 
                    if (!empty($art['Authors'])) {
                        echo htmlspecialchars(explode(', ', $art['Authors'])[0]); // Tác giả đầu tiên
                    } else {
                        echo 'Đang cập nhật';
                    }
                    ?>
                </p>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <hr class="divider">

    <section class="section">
        <div class="section__header">
            <h3>Truyện nhiều lượt xem nhất</h3>
        </div>

        <div class="card-list">
            <?php $rank = 1; foreach($articlesTop as $art): ?>
            <article class="card" onclick="window.location.href='<?= BASE_URL ?>truyen/<?= $art['ArticleID'] ?>'">
                <div class="card__thumb">
                    <?php if($art['CoverImage']): ?>
                        <img src="<?= getImageUrl($art['CoverImage']) ?>" alt="<?= htmlspecialchars($art['Title']) ?>">
                    <?php else: ?>
                        <div style="width:100%; height:100%; background:#333;"></div>
                    <?php endif; ?>
                    <span class="rank-number"><?= $rank ?></span>
                </div>
                <h4 class="card__title"><?= htmlspecialchars($art['Title']) ?></h4>
            </article>
            <?php $rank++; endforeach; ?>
        </div>
    </section>

</main>

<aside class="sidebar">
    <?php include 'includes/right_sidebar.php'; ?>
</aside>

<?php require_once 'includes/footer.php'; ?>