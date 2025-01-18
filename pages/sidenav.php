<link rel="stylesheet" href="../public/css/sidenav.css">
<link rel="stylesheet" href="../public/css/app.css">

<div id="mySidenav" class="sidenav">
    <a href="../index.php" data-translate="Home">Accueil</a>
    <a href="../pages/clan.php" data-translate="clan">Clans</a>
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <a href="../pages/mentions_legales.php" data-translate="mentions_legales">Mentions légales</a>


    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
    <div class="admin-menu">
        <a href="../pages/classements.php" data-translate="classements">
            <img src="../public/img/is_admin.png" alt="Admin Icon" style="vertical-align: middle; margin-right: 5px; width: 30px; height: 30px;">
            Voir les classements
        </a>

        <a href="../pages/creation_enigmes.php" data-translate="creation_enigmes">
            <img src="../public/img/is_admin.png" alt="Admin Icon" style="vertical-align: middle; margin-right: 5px; width: 30px; height: 30px;">
            Créer une énigme
        </a>

    </div>
<?php endif; ?>

    <div class="mode-switch">
        <label class="switch">
            <input type="checkbox" id="modeToggle" onclick="toggleMode()">
            <span class="slider round">
                <img src="../public/img/mode-nuit.png" class="night-icon">
                <img src="../public/img/mode-jour.png" class="day-icon">
            </span>
        </label>
    </div>
</div>

<script src="../public/js/sidenav.js"></script>