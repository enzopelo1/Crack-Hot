<?php
session_start();
?>

<link rel="stylesheet" href="../public/css/header.css">
<link rel="stylesheet" href="../public/css/app.css">
<script src="../public/js/sidenav.js"></script>
<link rel="icon" href="../public/img/logo_rond.png" type="image/png">

<header>
    <div class="header-content1">
        <?php if (isset($_SESSION['username'])): ?>
            <a href="../pages/logout.php">
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <img src="../public/img/is_admin.png" alt="Admin Profile Connection Logo">
                <?php else: ?>
                    <img src="../public/img/icon_login.png" alt="Profile Connection Logo">
                <?php endif; ?>
            </a>
            <p><span style="color: white; font-size: 20px">
                    <?php echo htmlspecialchars(ucfirst($_SESSION['username'])); ?>
                </span></p>
        <?php else: ?>
            <a href="../pages/login.php">
                <img src="../public/img/icon_login2.png" alt="Login Icon">
            </a>
        <?php endif; ?>
    </div>
    <div class="header-content1demi">
        <a href="../index.php">
            <img src="../public/img/Logo.png" alt="Home Logo">
        </a>
    </div>
    <div class="header-content2">
        <a href="javascript:void(0);" onclick="openNav()">
            <img id="settingsIcon" class="parametre" src="../public/img/parametres.png" alt="Settings Logo">
        </a>
    </div>
</header>