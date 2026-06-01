<?php
session_start();
require_once 'db.php';

$kat = isset($_GET['kat']) ? $_GET['kat'] : 'Tümü';
if ($kat != 'Tümü') {
    $s = $db->prepare("SELECT * FROM araclar WHERE kategori = ?");
    $s->execute([$kat]);
} else {
    $s = $db->prepare("SELECT * FROM araclar");
    $s->execute();
}
$araclar = $s->fetchAll(PDO::FETCH_ASSOC);
$bugun = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniChain Rent | Premium Filo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #0c0e17; color: #cbd5e1; font-family: system-ui, sans-serif; }
        .navbar { background-color: #131722; border-bottom: 1px solid #202636; }
        .car-card { background-color: #131722; border: 1px solid #202636; border-radius: 14px; overflow: hidden; transition: all 0.3s; }
        .car-card:hover { border-color: #00e676; transform: translateY(-4px); }
        .car-img-wrapper { height: 180px; display: flex; align-items: center; justify-content: center; background-color: #161b26; padding: 15px; }
        .car-img { max-height: 140px; max-width: 100%; object-fit: contain; filter: drop-shadow(0 8px 12px rgba(0,0,0,0.5)); }
        .filter-link { background-color: #1c2130; color: #94a3b8; border-radius: 8px; padding: 10px 22px; text-decoration: none; font-weight: 600; }
        .filter-link.active { background-color: #00e676; color: #000; }
        .badge-müsait { background-color: rgba(0, 230, 118, 0.15); color: #00e676; font-weight: 600; }
        .badge-kirada { background-color: rgba(255, 82, 82, 0.15); color: #ff5252; font-weight: 600; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark px-4 py-3 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-info fs-3" href="index.php"><i class="bi bi-car-front-fill text-warning"></i> UNICHAIN <span class="text-white fs-5">RENT</span></a>
        <div class="d-flex align-items-center">
            <?php if(isLoggedIn()): ?>
                <span class="text-secondary small me-3"><i class="bi bi-person-circle text-info"></i> Hoş geldin, <b><?= htmlspecialchars($_SESSION['kullanici_adi']) ?></b></span>
                <a href="dashboard.php" class="btn btn-info text-dark me-2 fw-bold px-4"><i class="bi bi-speedometer2"></i> Rezervasyon Masam</a>
                <a href="logout.php" class="btn btn-outline-danger fw-semibold">Çıkış</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-info fw-bold me-2 px-3">Giriş Yap</a>
                <a href="register.php" class="btn btn-info fw-bold text-dark px-4 shadow"><i class="bi bi-person-plus-fill"></i> Kayıt Ol</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container my-5">
    <!-- Filtre Sekmeleri -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="index.php?kat=Tümü" class="filter-link <?= $kat == 'Tümü' ? 'active' : '' ?>">Tüm Filo</a>
        <a href="index.php?kat=Ekonomik" class="filter-link <?= $kat == 'Ekonomik' ? 'active' : '' ?>">Ekonomik</a>
        <a href="index.php?kat=Konfor" class="filter-link <?= $kat == 'Konfor' ? 'active' : '' ?>">Konfor</a>
        <a href="index.php?kat=Premium" class="filter-link <?= $kat == 'Premium' ? 'active' : '' ?>">Premium</a>
    </div>

    <!-- Araç Kartları Grid Yapısı -->
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach($araclar as $a): 
            $resim_adi = 'egea.png';
            if ($a['marka'] == 'Renault') { $resim_adi = 'clio.png'; }
            elseif ($a['marka'] == 'Volkswagen') { $resim_adi = 'golf.png'; }
            elseif ($a['marka'] == 'Skoda') { $resim_adi = 'octavia.png'; }
            elseif ($a['marka'] == 'BMW') { $resim_adi = 'bmw3.png'; }
            elseif ($a['marka'] == 'Mercedes-Benz') { $resim_adi = 'mercedes.png'; }
            elseif ($a['marka'] == 'Audi') { $resim_adi = 'audi.png'; }
        ?>
        <div class="col">
            <div class="card car-card h-100 shadow-sm">
                <!-- img/ klasöründeki resimleri çeken alan -->
                <div class="car-img-wrapper">
                    <img src="img/<?= $resim_adi ?>" class="car-img" alt="<?= $a['marka'] ?>" onerror="this.src='https://via.placeholder.com/300x150/161b26/ffffff?text=Resim+Eksik'">
                </div>
                
                <div class="p-4 border-bottom border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-white fw-bold mb-0"><?= $a['marka'] ?></h4>
                        <small class="text-muted"><?= $a['model'] ?></small>
                    </div>
                    <span class="badge rounded-pill px-3 py-2 <?= $a['durum'] == 'Müsait' ? 'badge-müsait' : 'badge-kirada' ?>">
                        <?= $a['durum'] ?>
                    </span>
                </div>
                
                <div class="card-body p-4 pt-2">
                    <div class="mb-3">
                        <span class="text-secondary small d-block">Günlük Kiralama Bedeli:</span>
                        <span class="fs-4 fw-bold text-success"><?= number_format($a['gunluk_fiyat'], 2, ',', '.') ?> TL</span>
                    </div>

                    <div class="row g-2 mb-3 text-secondary small border-top border-secondary border-opacity-10 pt-2">
                        <div class="col-6"><i class="bi bi-gear-fill text-info"></i> <?= $a['vites'] ?></div>
                        <div class="col-6"><i class="bi bi-fuel-pump-fill text-info"></i> <?= $a['yakit'] ?></div>
                    </div>

                    <!-- TARİH SEÇİMLİ AKTİF KİRALAMA FORMU -->
                    <?php if($a['durum'] == 'Müsait'): ?>
                        <form action="dashboard.php" method="POST" class="border-top border-secondary border-opacity-10 pt-3">
                            <input type="hidden" name="action" value="rent">
                            <input type="hidden" name="arac_id" value="<?= $a['id'] ?>">
                            
                            <div class="mb-2">
                                <label class="text-secondary small" style="font-size: 11px;">Alış Tarihi:</label>
                                <input type="date" name="alis_tarihi" class="form-control form-control-sm bg-dark text-white border-secondary" min="<?= $bugun ?>" value="<?= $bugun ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-secondary small" style="font-size: 11px;">İade Tarihi:</label>
                                <input type="date" name="iade_tarihi" class="form-control form-control-sm bg-dark text-white border-secondary" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-sm btn-info text-dark fw-bold w-100 py-2"><i class="bi bi-calendar-check-fill"></i> Tarihleri Onayla ve Kirala</button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-sm btn-secondary w-100 py-2 disabled mt-4">Bu Araç Şu An Kirada</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>