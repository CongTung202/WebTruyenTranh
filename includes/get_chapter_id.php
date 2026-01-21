<?php
require_once 'db.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
$num = $_GET['num'] ?? null;

if(!$id || !$num) {
    echo json_encode(['status' => 'error', 'message' => 'Tham số không hợp lệ']);
    exit;
}

try {
    // Lấy chapter có Index = $num
    $sql = "SELECT ChapterID FROM chapters WHERE ArticleID = ? AND `Index` = ? AND IsDeleted = 0 LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, (int)$num]);
    $chapter = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($chapter) {
        echo json_encode([
            'status' => 'success',
            'chapter_id' => $chapter['ChapterID']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Không tìm thấy chapter ' . (int)$num
        ]);
    }
    
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
