<?php
session_start();
require_once 'db.php';
$hata = ""; $basari = "";

if (isLoggedIn()) { header("Location: dashboard.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['kullanici_adi']);
    $pass = trim($_POST['sifre']);
    $pass_again = trim($_POST['sifre_tekrar']);

    if (!empty($user) && !empty($pass) && !empty($pass_again)) {
        if ($pass === $pass_again) {
            if (strlen($pass) >= 4) {
                // Kullanıcı adı zaten var mı kontrolü
                $kontrol = $db->prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = ?");
                $kontrol->execute([$user]);
                
                if ($kontrol->rowCount() == 0) {
                    // MD5 şifreleme ve Veritabanı kaydı
                    $sifreli_pass = md5($pass);
                    $kayit = $db->prepare("INSERT INTO kullanicilar (kullanici_adi, sifre) VALUES (?, ?)");
                    
                    if ($kayit->execute([$user, $sifreli_pass])) {
                        $basari = "Hesabınız başarıyla oluşturuldu! Giriş yapabilirsiniz.";
                    } else { $hata = "Kayıt esnasında teknik bir sorun oluştu."; }
                } else { $hata = "Bu kullanıcı adı zaten sistemde kayıtlı!"; }
            } else { $hata = "Şifre en az 4 karakterden oluşmalıdır!"; }
        } else { $hata = "Girdiğiniz şifreler birbiriyle uyuşmuyor!"; }
    } else { $hata = "Lütfen tüm alanları eksiksiz doldurun!"; }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Müşteri Kayıt Paneli | UniChain Rent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #0b0c14; color: #cbd5e1; height: 100vh; display: flex; align-items: center; justify-content: center;">
<div style="background-color: #121623; border: 1px solid #222b45; width: 100%; max-width: 400px; border-radius: 16px; padding: 35px;" class="shadow-lg">
    <h4 class="text-center text-info fw-bold mb-1">MÜŞTERİ KAYDI</h4>
    <p class="text-muted text-center small mb-4">Yeni bir hesap oluşturun</p>
    
    <?php if(!empty($hata)): ?><div class="alert alert-danger p-2 small text-center"><?= $hata ?></div><?php endif; ?>
    <?php if(!empty($basari)): ?><div class="alert alert-success p-2 small text-center"><?= $basari ?></div><?php endif; ?>
    
    <form action="register.php" method="POST">
        <div class="mb-3">
            <label class="small text-secondary mb-1">Kullanıcı Adı</label>
            <input type="text" name="kullanici_adi" class="form-control bg-dark text-white border-secondary" required placeholder="Örn: emir44">
        </div>
        <div class="mb-3">
            <label class="small text-secondary mb-1">Şifre</label>
            <input type="password" name="sifre" class="form-control bg-dark text-white border-secondary" required placeholder="******">
        </div>
        <div class="mb-4">
            <label class="small text-secondary mb-1">Şifre Tekrar</label>
            <input type="password" name="sifre_tekrar" class="form-control bg-dark text-white border-secondary" required placeholder="******">
        </div>
        <button type="submit" class="btn btn-info w-100 fw-bold text-dark py-2 mb-3">Hesabı Oluştur</button>
        <div class="text-center"><a href="login.php" class="text-info small text-decoration-none">Zaten hesabım var, Giriş Yap</a></div>
    </form>
</div>
</body>
</html>