<?php
require_once 'db.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
$sort = $_GET['sort'] ?? 'desc';
$page = $_GET['page'] ?? 1;

$sortOrder = ($sort === 'asc') ? 'ASC' : 'DESC';
$ITEMS_PER_PAGE = 10;
$currentPage = max(1, (int)$page);

if(!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Article ID không hợp lệ']);
    exit;
}

try {
    // Lấy tổng số chapter
    $sqlTotal = "SELECT COUNT(*) as total FROM chapters WHERE ArticleID = ? AND IsDeleted = 0";
    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->execute([$id]);
    $totalChapters = $stmtTotal->fetch()['total'];
    $totalPages = ceil($totalChapters / $ITEMS_PER_PAGE);
    
    if($currentPage > $totalPages && $totalPages > 0) {
        $currentPage = $totalPages;
    }
    
    $offset = ($currentPage - 1) * $ITEMS_PER_PAGE;
    
    // Lấy chapters
    $sql = "SELECT c.ChapterID, c.`Index`, c.Title, c.CreatedAt,
            (SELECT ImageURL FROM chapter_images ci 
             WHERE ci.ChapterID = c.ChapterID 
             ORDER BY ci.ImageID ASC LIMIT 1) as ChapterThumb 
            FROM chapters c 
            WHERE c.ArticleID = ? AND c.IsDeleted = 0 
            ORDER BY c.`Index` $sortOrder
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $ITEMS_PER_PAGE, $offset]);
    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data trả về
    $chaptersData = array_map(function($chap) {
        return [
            'id' => $chap['ChapterID'],
            'index' => $chap['Index'],
            'title' => htmlspecialchars($chap['Title'] ?? ''),
            'thumb' => $chap['ChapterThumb'] ? (strpos($chap['ChapterThumb'], 'http') === 0 ? $chap['ChapterThumb'] : BASE_URL . $chap['ChapterThumb']) : '',
            'date' => date('y.m.d', strtotime($chap['CreatedAt']))
        ];
    }, $chapters);
    
    echo json_encode([
        'status' => 'success',
        'chapters' => $chaptersData,
        'totalChapters' => $totalChapters,
        'totalPages' => $totalPages,
        'currentPage' => $currentPage
    ]);
    
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
