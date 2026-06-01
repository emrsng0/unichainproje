<?php
session_start();
require_once 'db.php';

if (!isLoggedIn()) { header("Location: login.php"); exit; }

$kullanici_id = $_SESSION['kullanici_id'];
$alert = ""; $status = "";

// 🚗 CRUD - CREATE: REZERVASYON YAPMA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'rent') {
    $arac_id = intval($_POST['arac_id']);
    $alis_tarihi = $_POST['alis_tarihi'];
    $iade_tarihi = $_POST['iade_tarihi'];

    $tarih1 = new DateTime($alis_tarihi);
    $tarih2 = new DateTime($iade_tarihi);
    $interval = $tarih1->diff($tarih2);
    $gun = intval($interval->days);

    if($tarih2 > $tarih1 && $gun > 0) {
        $a_stmt = $db->prepare("SELECT gunluk_fiyat, durum FROM araclar WHERE id = ?");
        $a_stmt->execute([$arac_id]); $arac = $a_stmt->fetch();
        
        if($arac && $arac['durum'] == 'Müsait') {
            $toplam_tutar = $arac['gunluk_fiyat'] * $gun;
            
            $k_stmt = $db->prepare("SELECT bakiye FROM kullanicilar WHERE id = ?");
            $k_stmt->execute([$kullanici_id]); $kullanici = $k_stmt->fetch();

            if($kullanici['bakiye'] >= $toplam_tutar) {
                $db->prepare("UPDATE kullanicilar SET bakiye = bakiye - ? WHERE id = ?")->execute([$toplam_tutar, $kullanici_id]);
                $db->prepare("UPDATE araclar SET durum = 'Kirada' WHERE id = ?")->execute([$arac_id]);
                
                $db->prepare("INSERT INTO rezervasyonlar (kullanici_id, arac_id, alis_tarihi, iade_tarihi, gun_sayisi, toplam_tutar) VALUES (?, ?, ?, ?, ?, ?)")
                   ->execute([$kullanici_id, $arac_id, $alis_tarihi, $iade_tarihi, $gun, $toplam_tutar]);
                
                $alert = "Kiralama Sözleşmesi Başarıyla Onaylandı! Süre: " . $gun . " Gün. Maliyet: " . number_format($toplam_tutar, 2, ',', '.') . " TL."; $status = "success";
            } else { $alert = "Cüzdan bakiyeniz yetersiz! Toplam tutar: " . number_format($toplam_tutar, 2, ',', '.') . " TL."; $status = "danger"; }
        }
    } else { $alert = "Hata: İade tarihi, alış tarihinden sonraki bir gün olmalıdır!"; $status = "danger"; }
}

// 🚗 CRUD - DELETE: İPTAL ETME
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $rez_id = intval($_GET['id']);
    $r_stmt = $db->prepare("SELECT * FROM rezervasyonlar WHERE id = ? AND kullanici_id = ?");
    $r_stmt->execute([$rez_id, $kullanici_id]); $rez = $r_stmt->fetch();

    if($rez) {
        $db->prepare("UPDATE kullanicilar SET bakiye = bakiye + ? WHERE id = ?")->execute([$rez['toplam_tutar'], $kullanici_id]);
        $db->prepare("UPDATE araclar SET durum = 'Müsait' WHERE id = ?")->execute([$rez['arac_id']]);
        $db->prepare("DELETE FROM rezervasyonlar WHERE id = ?")->execute([$rez_id]);
        $alert = "Sözleşme feshedildi, ödediğiniz tutar hesabınıza iade edildi."; $status = "success";
    }
}

$userdata = $db->query("SELECT bakiye FROM kullanicilar WHERE id = $kullanici_id")->fetch();
$my_rentals = $db->query("SELECT r.id, r.alis_tarihi, r.iade_tarihi, r.gun_sayisi, r.toplam_tutar, a.marka, a.model FROM rezervasyonlar r JOIN araclar a ON r.arac_id = a.id WHERE r.kullanici_id = $kullanici_id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Müşteri Paneli | Sözleşmelerim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #0c0e17; color: #cbd5e1;">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-3">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-info" href="index.php">UNICHAIN DESK</a>
        <a href="index.php" class="btn btn-sm btn-secondary">Araç Vitrinine Dön</a>
    </div>
</nav>

<div class="container my-5">
    <?php if(!empty($alert)): ?><div class="alert alert-<?= $status ?> border-0 shadow"><?= $alert ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div style="background-color: #131722; border: 1px solid #202636; border-radius: 12px;" class="p-4 shadow-sm">
                <span class="text-secondary small fw-bold text-uppercase">Cüzdan Alım Gücü</span>
                <h2 class="text-info fw-bold my-1"><?= number_format($userdata['bakiye'], 2, ',', '.') ?> TL</h2>
            </div>
        </div>

        <div class="col-md-8">
            <div style="background-color: #131722; border: 1px solid #202636; border-radius: 12px;" class="p-4 shadow-sm">
                <h5 class="fw-bold text-white mb-3">Aktif Kiralama Rezervasyonlarım</h5>
                <?php if(count($my_rentals) == 0): ?>
                    <p class="text-muted small">Şu an aktif bir sözleşmeniz bulunmuyor.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark small">
                            <thead>
                                <tr>
                                    <th>Araç</th>
                                    <th>Alış Tarihi</th>
                                    <th>İade Tarihi</th>
                                    <th>Süre</th>
                                    <th>Toplam Tutar</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($my_rentals as $mr): ?>
                                <tr>
                                    <td class="text-info fw-bold"><?= htmlspecialchars($mr['marka']) ?> <?= htmlspecialchars($mr['model']) ?></td>
                                    <td><?= date('d.m.Y', strtotime($mr['alis_tarihi'])) ?></td>
                                    <td><?= date('d.m.Y', strtotime($mr['iade_tarihi'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= $mr['gun_sayisi'] ?> Gün</span></td>
                                    <td class="text-success fw-bold"><?= number_format($mr['toplam_tutar'], 2, ',', '.') ?> TL</td>
                                    <td class="text-end"><a href="dashboard.php?action=cancel&id=<?= $mr['id'] ?>" class="btn btn-sm btn-danger py-1 px-2" onclick="return confirm('Sözleşmeyi iptal etmek istediğinize emin misiniz?')">İptal Et</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>