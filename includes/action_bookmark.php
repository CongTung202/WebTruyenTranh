<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$articleId = $_GET['id'] ?? null;
$isAjax = isset($_GET['ajax']); // Kiểm tra xem có phải gọi từ AJAX không

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    if ($isAjax) {
        echo json_encode(['status' => 'login_required']);
        exit;
    } else {
        header("Location: ../login.php");
        exit;
    }
}

$userId = $_SESSION['user_id'];

if ($articleId) {
    // 2. Kiểm tra đã bookmark chưa
    $stmtCheck = $pdo->prepare("SELECT BookmarkID FROM bookmarks WHERE UserID = ? AND ArticleID = ?");
    $stmtCheck->execute([$userId, $articleId]);
    $bookmark = $stmtCheck->fetch();

    if ($bookmark) {
        // Nếu ĐÃ có -> XÓA (Hủy theo dõi)
        $pdo->prepare("DELETE FROM bookmarks WHERE BookmarkID = ?")->execute([$bookmark['BookmarkID']]);
        $bookmarked = false;
        $msg = "Đã hủy theo dõi.";
    } else {
        // Nếu CHƯA có -> THÊM (Theo dõi)
        $pdo->prepare("INSERT INTO bookmarks (UserID, ArticleID, CreatedAt) VALUES (?, ?, NOW())")->execute([$userId, $articleId]);
        $bookmarked = true;
        $msg = "Đã thêm vào danh sách theo dõi.";
    }

    // 3. Phản hồi
    if ($isAjax) {
        // Trả về JSON cho Javascript
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'is_bookmarked' => $bookmarked,
            'message' => $msg
        ]);
        exit;
    } else {
        // Chuyển hướng (Dành cho các trang cũ không dùng AJAX)
        $redirect = $_GET['redirect'] ?? 'detail';
        if ($redirect == 'read') {
            $chap = $_GET['chap'] ?? '';
            header("Location: ../read.php?id=$articleId&chap=$chap");
        } else {
            header("Location: ../detail.php?id=$articleId");
        }
        exit;
    }
}
?>