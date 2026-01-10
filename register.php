<?php
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validate cơ bản
    if ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        // 2. Kiểm tra User đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT UserID FROM users WHERE UserName = ? OR Email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Tên đăng nhập hoặc Email đã được sử dụng.";
        } else {
            // [THAY ĐỔI Ở ĐÂY] Đặt ảnh mặc định
            // Lưu ý: Bạn cần upload file 'defaultavatar.png' lên thư mục gốc (public_html)
            $defaultAvatar = 'default/defaultavatar.png'; 

            // 3. Thêm vào DB (Thêm cột Avatar vào câu lệnh INSERT)
            $sql = "INSERT INTO users (UserName, Email, Password, Role, Avatar) VALUES (?, ?, ?, 0, ?)";
            $stmtInsert = $pdo->prepare($sql);
            
            // Truyền thêm biến $defaultAvatar vào mảng execute
            if ($stmtInsert->execute([$username, $email, $password, $defaultAvatar])) {
                $success = "Đăng ký thành công! Đang chuyển hướng...";
                echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - GTSCHUNDER</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css?v=<?= time() ?>">
</head>
<body>

    <div class="lang-dropdown">
        English <i class="fas fa-chevron-down ms-1"></i>
    </div>

    <div class="login-wrapper">
        
        <h1 class="login-logo">GTSC<strong style="color: #506891;">HUNDER</strong></h1>

        <div class="login-card">
            
            <div class="login-tabs">
                <a href="login.php" class="tab-item inactive">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </a>
                <div class="tab-item active">
                    <i class="fas fa-user-plus"></i> Đăng ký
                </div>
            </div>

            <div class="login-body">
                
                <?php if($error): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="alert-success" style="background: rgba(46, 204, 113, 0.15); color: #2ecc71; padding: 10px; border: 1px solid #2ecc71; border-radius: 4px; margin-bottom: 20px; font-size: 13px; text-align: center;">
                        <i class="fas fa-check-circle me-2"></i> <?= $success ?>
                    </div>
                <?php else: ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Tên đăng nhập</label>
                        <input type="text" name="username" class="form-input" required autocomplete="off" placeholder="VD: user123 (Viết liền không dấu)">
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ Email</label>
                        <input type="email" name="email" class="form-input" required autocomplete="off" placeholder="VD: example@gmail.com">
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <input type="password" name="password" class="form-input" required placeholder="Nhập mật khẩu của bạn">
                    </div>

                    <div class="form-group">
                        <label>Xác nhận mật khẩu</label>
                        <input type="password" name="confirm_password" class="form-input" required placeholder="Nhập lại mật khẩu bên trên">
                    </div>

                    <button type="submit" class="btn-submit" style="margin-top: 10px;">Đăng ký tài khoản</button>
                    
                    <div class="login-footer-link" style="text-align: center; margin-top: 15px; font-size: 13px; color: #666;">
                        Đã có tài khoản? <a href="login.php" style="color: #506891; font-weight: bold;">Đăng nhập ngay</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer-text">
            <strong>GTSCHUNDER</strong> Copyright © <strong>GTSCHUNDER Corp.</strong> All Rights Reserved.
        </div>
    </div>

</body>
</html>