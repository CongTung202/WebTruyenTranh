<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$userId = $_SESSION['user_id'];

// --- [PHẦN 1] XỬ LÝ AJAX (Xóa bình luận) ---
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'delete_comment') {
    $commentId = $_POST['comment_id'] ?? 0;
    
    // Kiểm tra quyền sở hữu comment
    $stmtCheck = $pdo->prepare("SELECT CommentID FROM comments WHERE CommentID = ? AND UserID = ?");
    $stmtCheck->execute([$commentId, $userId]);
    
    if ($stmtCheck->rowCount() > 0) {
        $pdo->prepare("UPDATE comments SET IsDeleted = 1 WHERE CommentID = ?")->execute([$commentId]);
        echo json_encode(['status' => 'success', 'message' => 'Đã xóa bình luận.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền xóa.']);
    }
    exit; // Dừng code PHP tại đây để trả về JSON
}

// --- [PHẦN 2] XỬ LÝ UPLOAD AVATAR (Form Submit thường) ---
$uploadMessage = '';
$uploadStatus = ''; // success hoặc error

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar_file'])) {
    $avatarUrl = uploadImageToCloud($_FILES['avatar_file'], 'avatars');

    if ($avatarUrl) {
        $stmt = $pdo->prepare("UPDATE users SET Avatar = ? WHERE UserID = ?");
        $stmt->execute([$avatarUrl, $userId]);
        $_SESSION['avatar'] = $avatarUrl;
        
        $uploadMessage = "Cập nhật ảnh đại diện thành công!";
        $uploadStatus = 'success';
    } else {
        $uploadMessage = "Lỗi khi tải ảnh lên. Vui lòng thử lại.";
        $uploadStatus = 'error';
    }
}

// Lấy thông tin User
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE UserID = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// Lấy lịch sử bình luận
$sqlCmt = "SELECT c.*, a.Title AS ArticleTitle, a.ArticleID 
           FROM comments c 
           JOIN articles a ON c.ArticleID = a.ArticleID 
           WHERE c.UserID = ? AND c.IsDeleted = 0 
           ORDER BY c.CreatedAt DESC";
$stmtCmt = $pdo->prepare($sqlCmt);
$stmtCmt->execute([$userId]);
$myComments = $stmtCmt->fetchAll();
$totalComments = count($myComments);
$pageTitle = "Hồ sơ cá nhân";
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>css/profile.css?v=<?= time() ?>">



<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/profile.css?v=<?= time() ?>">

<div class="toast-container" id="toastContainer"></div>

<div class="main-container">
    <div class="profile-container">
        
        <div class="profile-box">
            <div class="profile-banner"></div>

            <div class="profile-info">
                <div class="avatar-wrapper">
                    <img src="<?= getImageUrl($_SESSION['avatar']) ?>" class="avatar-img">
                    
                    <form id="avatarForm" method="POST" enctype="multipart/form-data" style="display: none;">
                        <input type="file" name="avatar_file" id="avatarInput" accept="image/*">
                    </form>

                    <button class="btn-upload-cam" type="button" onclick="document.getElementById('avatarInput').click();" title="Đổi ảnh đại diện">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>

                <h4 class="user-name"><?= htmlspecialchars($user['UserName']) ?></h4>
                <p class="user-email"><?= htmlspecialchars($user['Email']) ?></p>
                
                <span class="user-badge <?= $user['Role'] == 1 ? 'badge-admin' : 'badge-member' ?>">
                    <?= $user['Role'] == 1 ? 'Quản trị viên' : 'Thành viên' ?>
                </span>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-val"><?= $totalComments ?></span>
                        <span class="stat-label">Bình luận</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-val"><?= date('d/m/y', strtotime($user['CreatedAt'] ?? 'now')) ?></span>
                        <span class="stat-label">Tham gia</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="activity-box">
            <div class="box-heading">
                <i class="fas fa-history" style="color: var(--primary-theme)"></i> Hoạt động gần đây
            </div>
            
            <div class="activity-list" id="commentList">
                <?php if ($totalComments > 0): ?>
                    <?php foreach ($myComments as $cmt): ?>
                        <div class="activity-item" id="cmt-row-<?= $cmt['CommentID'] ?>">
                            <div class="act-header">
                                <a href="<?= BASE_URL ?>truyen/<?= $cmt['ArticleID'] ?>" class="act-manga-title">
                                    <i class="fas fa-book-open"></i> <?= htmlspecialchars($cmt['ArticleTitle']) ?>
                                </a>
                                
                                <div class="act-meta">
                                    <span class="time" title="<?= $cmt['CreatedAt'] ?>">
                                        <i class="far fa-clock"></i> <?= date('d/m/Y', strtotime($cmt['CreatedAt'])) ?>
                                    </span>
                                    
                                    <button class="btn-del-cmt" onclick="deleteComment(<?= $cmt['CommentID'] ?>)" title="Xóa bình luận">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="act-body">
                                <?= nl2br(htmlspecialchars($cmt['Content'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="far fa-comment-dots"></i>
                        <p>Bạn chưa có bình luận nào.</p>
                        <a href="<?= BASE_URL ?>" style="color: var(--primary-theme); margin-top: 10px; display:inline-block;">Đi đọc truyện ngay</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
    // 1. Tự động Upload khi chọn ảnh
    document.getElementById('avatarInput').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            document.getElementById('avatarForm').submit();
        }
    });

    // 2. Hàm hiển thị Toast Notification
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        const color = type === 'success' ? '#2ecc71' : '#ff4d4d';
        
        const toast = document.createElement('div');
        toast.className = `custom-toast ${type}`;
        toast.innerHTML = `
            <i class="fas ${icon}" style="color: ${color}"></i>
            <span>${message}</span>
        `;
        
        container.appendChild(toast);
        
        // Hiện lên
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Tự tắt sau 3s
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // 3. Hàm Xóa Comment bằng AJAX
    function deleteComment(id) {
        if (!confirm('Bạn chắc chắn muốn xóa bình luận này?')) return;

        const formData = new FormData();
        formData.append('ajax_action', 'delete_comment');
        formData.append('comment_id', id);

        fetch('profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Xóa dòng đó khỏi giao diện
                const row = document.getElementById('cmt-row-' + id);
                if (row) {
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Có lỗi xảy ra', 'error');
        });
    }

    // 4. Hiển thị thông báo Upload (nếu có từ PHP)
    <?php if ($uploadMessage): ?>
        showToast("<?= $uploadMessage ?>", "<?= $uploadStatus ?>");
    <?php endif; ?>
function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        const color = type === 'success' ? '#2ecc71' : '#ff4d4d';
        const toast = document.createElement('div');
        toast.className = `custom-toast ${type}`;
        toast.innerHTML = `<i class="fas ${icon}" style="color: ${color}"></i><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
    }

    function deleteComment(id) {
        if (!confirm('Bạn chắc chắn muốn xóa bình luận này?')) return;
        const formData = new FormData();
        formData.append('ajax_action', 'delete_comment');
        formData.append('comment_id', id);
        fetch('profile.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const row = document.getElementById('cmt-row-' + id);
                if (row) { row.style.opacity = '0'; row.style.transform = 'translateX(20px)'; setTimeout(() => row.remove(), 300); }
                showToast(data.message, 'success');
            } else { showToast(data.message, 'error'); }
        })
        .catch(err => { console.error(err); showToast('Có lỗi xảy ra', 'error'); });
    }

    <?php if ($uploadMessage): ?>
        showToast("<?= $uploadMessage ?>", "<?= $uploadStatus ?>");
    <?php endif; ?>

</script>

<?php require_once 'includes/footer.php'; ?>