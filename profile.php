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

$pageTitle = "Hồ sơ cá nhân";
require_once 'includes/header.php';
?>

<style>
    /* 1. Layout Profile - Cân Đối & Responsive */
    .profile-container { 
        display: flex; 
        gap: 40px; 
        margin-top: 40px; 
        margin-bottom: 60px;
        justify-content: center;
        align-items: flex-start; 
        max-width: 1200px;
        margin-left: auto; 
        margin-right: auto;
        padding: 0 20px;
    }
    
    /* Box User - Sidebar */
    .profile-box { 
        width: 280px;
        flex-shrink: 0;
        background: var(--bg-element); 
        padding: 40px 25px; 
        border-radius: 10px; 
        text-align: center; 
        border: 1px solid var(--border-color);
        position: sticky;
        top: 100px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* Box Hoạt động - Content Area */
    .activity-box { 
        flex: 1;
        min-width: 400px;
        background: var(--bg-element); 
        border-radius: 10px; 
        border: 1px solid var(--border-color);
        overflow: hidden;
        min-height: 600px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Avatar Styles - Cải thiện */
    .avatar-wrapper { 
        position: relative; 
        display: inline-block; 
        margin-bottom: 25px; 
    }
    .avatar-img { 
        width: 140px; 
        height: 140px; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 5px solid var(--bg-body); 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s;
    }
    .avatar-img:hover { transform: scale(1.05); }
    
    .btn-upload-cam {
        position: absolute;
        bottom: 8px;
        right: 8px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-theme);
        border: 2px solid var(--bg-element);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.3s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    .btn-upload-cam:hover { 
        transform: scale(1.15);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    /* Text Info - User Details */
    .user-name { 
        color: var(--text-main); 
        font-weight: bold; 
        font-size: 22px; 
        margin-bottom: 8px;
        word-break: break-word;
    }
    .user-email { 
        color: var(--text-muted); 
        font-size: 12px; 
        margin-bottom: 16px;
        word-break: break-all;
    }
    .user-role { 
        display: inline-block; 
        padding: 6px 14px; 
        background: rgba(80, 104, 145, 0.15);
        color: var(--primary-theme);
        border: 1.5px solid var(--primary-theme);
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        letter-spacing: 0.5px;
        margin-top: 8px;
    }

    /* Activity List - Bình luận */
    .activity-header { 
        padding: 20px 25px; 
        border-bottom: 2px solid var(--border-color);
        font-weight: bold;
        color: var(--text-main);
        font-size: 17px;
        background: rgba(255, 255, 255, 0.02);
    }
    
    .activity-item { 
        padding: 22px 25px; 
        border-bottom: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        transition: background 0.2s;
    }
    .activity-item:last-child { border-bottom: none; }
    .activity-item:hover { 
        background: var(--bg-hover);
    }

    .act-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .act-link { 
        color: var(--primary-theme); 
        font-weight: bold; 
        font-size: 14px; 
        text-decoration: none;
        flex: 1;
    }
    .act-link:hover { 
        text-decoration: underline; 
    }
    .act-content { 
        color: var(--text-main); 
        font-size: 13px; 
        margin-top: 8px; 
        line-height: 1.6;
        word-break: break-word;
    }
    .act-time { 
        color: var(--text-muted); 
        font-size: 11px; 
        margin-top: 10px; 
        display: block; 
    }

    /* Nút xóa AJAX - Bên phải */
    .btn-del-cmt { 
        color: #ff4d4d;
        font-size: 11px;
        padding: 6px 12px;
        border-radius: 4px;
        border: 1px solid transparent;
        background: transparent;
        cursor: pointer;
        transition: 0.2s;
        white-space: nowrap;
        margin-left: auto;
        flex-shrink: 0;
    }
    .btn-del-cmt:hover { 
        background: rgba(255, 77, 77, 0.15);
        border-color: #ff4d4d;
    }

    /* --- TOAST NOTIFICATION --- */
    .toast-container {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 9999;
    }
    .custom-toast {
        background: var(--bg-element);
        color: var(--text-main);
        padding: 15px 20px;
        border-radius: 6px;
        border-left: 4px solid var(--primary-theme);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        margin-bottom: 10px;
        min-width: 300px;
    }
    .custom-toast.show { transform: translateX(0); }
    .custom-toast.success { border-left-color: #2ecc71; }
    .custom-toast.error { border-left-color: #ff4d4d; }
    .custom-toast i { font-size: 18px; }

    /* Responsive */
    @media (max-width: 1024px) {
        .profile-container {
            gap: 30px;
        }
        .profile-box {
            width: 260px;
        }
        .activity-box {
            min-width: auto;
        }
    }

    @media (max-width: 768px) {
        .profile-container { 
            flex-direction: column;
            gap: 30px;
            padding: 0 15px;
        }
        .profile-box { 
            width: 100%;
            max-width: none;
            position: static;
            padding: 30px 20px;
        }
        .activity-box { 
            width: 100%;
            min-width: auto;
            min-height: auto;
        }
        .btn-del-cmt {
            margin-left: 0;
            margin-top: 10px;
        }
    }
</style>

<div class="toast-container" id="toastContainer"></div>

<div class="main-container">
    <div class="profile-container">
        
        <div class="profile-box">
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
            <span class="user-role"><?= $user['Role'] == 1 ? 'ADMINISTRATOR' : 'MEMBER' ?></span>
        </div>

        <div class="activity-box">
            <div class="activity-header">
                <i class="fas fa-history me-2"></i> Lịch sử bình luận
            </div>
            
            <div class="activity-list" id="commentList">
                <?php if (count($myComments) > 0): ?>
                    <?php foreach ($myComments as $cmt): ?>
                        <div class="activity-item" id="cmt-row-<?= $cmt['CommentID'] ?>">
                            <div class="act-header">
                                <a href="<?= BASE_URL ?>truyen/<?= $cmt['ArticleID'] ?>" class="act-link">
                                    <?= htmlspecialchars($cmt['ArticleTitle']) ?>
                                </a>
                                <button class="btn-del-cmt" onclick="deleteComment(<?= $cmt['CommentID'] ?>)" title="Xóa bình luận">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="act-content"><?= nl2br(htmlspecialchars($cmt['Content'])) ?></div>
                            <span class="act-time"><?= date('d/m/Y H:i', strtotime($cmt['CreatedAt'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 50px; text-align: center; color: var(--text-muted);">
                        <i class="far fa-comment-dots" style="font-size: 40px; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                        Chưa có hoạt động nào.
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
</script>

<?php require_once 'includes/footer.php'; ?>