<?php
session_start();
require_once 'db.php';
$hata = "";

if (isLoggedIn()) { header("Location: dashboard.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['kullanici_adi']);
    $pass = trim($_POST['sifre']);

    if (!empty($user) && !empty($pass)) {
        // Giriş şifresini MD5 ile sıkıştırıp kontrol etme
        $sifreli_pass = md5($pass);
        $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = ? AND sifre = ?");
        $stmt->execute([$user, $sifreli_pass]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $_SESSION['kullanici_id'] = $row['id'];
            $_SESSION['kullanici_adi'] = $row['kullanici_adi'];
            header("Location: dashboard.php");
            exit;
        } else { $hata = "Kullanıcı adı veya şifre hatalı!"; }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap | UniChain Rent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #0b0c14; color: #cbd5e1; height: 100vh; display: flex; align-items: center; justify-content: center;">
<div style="background-color: #121623; border: 1px solid #222b45; width: 100%; max-width: 380px; border-radius: 16px; padding: 35px;" class="shadow-lg">
    <h4 class="text-center text-info fw-bold mb-4">MÜŞTERİ GİRİŞİ</h4>
    <?php if(!empty($hata)): ?><div class="alert alert-danger p-2 small text-center"><?= $hata ?></div><?php endif; ?>
    <form action="login.php" method="POST">
        <div class="mb-3"><label class="small text-secondary mb-1">Kullanıcı Adı</label><input type="text" name="kullanici_adi" class="form-control bg-dark text-white border-secondary" placeholder="emir" required></div>
        <div class="mb-4"><label class="small text-secondary mb-1">Şifre</label><input type="password" name="sifre" class="form-control bg-dark text-white border-secondary" placeholder="******" required></div>
        <button type="submit" class="btn btn-info w-100 fw-bold text-dark py-2 mb-3">Oturum Aç</button>
        <div class="text-center"><a href="register.php" class="text-info small text-decoration-none">Yeni hesap aç, Kayıt Ol</a></div>
    </form>
</div>
</body>
</html>