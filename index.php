<?php session_start(); $ephVers="v 0.3.0"?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="style.css" rel="stylesheet">
    <title>Sqlite</title>
    <script src="htmx.min.js"></script>
  </head>
  <body id='page'>
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" width="100px" height="0px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve">
        <defs>
            <path id="svg_home" d="M13.63 0c-3.325 0-3.948 4.735-2.078 4.735S16.954 0 13.629 0zM8.11 4.148c.995.06 2.088-3.8.217-3.557-1.871.243-1.213 3.496-.217 3.557zM3.547 6.547c.708-.312.09-3.36-1.113-2.53-1.204.832.405 2.841 1.113 2.53zM5.542 4.95c.843-.171.89-3.629-.614-3.009-1.504.62-.228 3.182.614 3.01zM9.944 12.689c.15 1.142-.838 1.706-1.805.97-3.08-2.344 5.097-3.513 4.558-6.716-.447-2.658-8.603-1.84-9.532 2.32-.628 2.813 2.588 6.716 5.944 6.716 1.651 0 3.556-1.491 3.912-3.38.272-1.44-3.202-.863-3.077.09z"/>
            <path id="add" d="M4.49 8.994a.5.5 0 0 0-.406.229l-1 1.5a.5.5 0 1 0 .832.554l1-1.5a.5.5 0 0 0-.426-.783zm7.004 0a.5.5 0 0 0-.41.783l1 1.5a.5.5 0 1 0 .832-.554l-1-1.5a.5.5 0 0 0-.422-.229z
            M7 1v5.563L5.719 5.28A1.015 1.015 0 0 0 5 5H4v1c0 .265.093.53.281.719l3 3 .282.281h.875l.28-.281 3-3C11.908 6.53 12 6.265 12 6V5h-1c-.265 0-.53.093-.719.281L9 6.563V1z
            M3 11v4h10v-4zm3.344 1.438a.651.651 0 0 1 .062 0c.291-.056.6.203.594.5V13h2v-.063a.52.52 0 0 1 .5-.507.52.52 0 0 1 .5.507V13c0 .545-.455 1-1 1H7c-.545 0-1-.455-1-1v-.063a.515.515 0 0 1 .344-.5z"/>
            <path id="search" d="M6.508 1C3.48 1 1.002 3.473 1.002 6.5c0 3.026 2.478 5.5 5.506 5.5s5.504-2.474 5.504-5.5c0-3.027-2.476-5.5-5.504-5.5zm0 2a3.486 3.486 0 0 1 3.504 3.5c0 1.944-1.556 3.5-3.504 3.5a3.488 3.488 0 0 1-3.506-3.5C3.002 4.555 4.56 3 6.508 3z
            M10 8.99a1 1 0 0 0-.695 1.717l4.004 4a1 1 0 1 0 1.414-1.414l-4.004-4A1 1 0 0 0 10 8.99z"/>
            <path id="categories" d="M8 4h6v2H8z
            M8 9.984h6v2H8z
            M2.384 3h3.231A.38.38 0 0 1 6 3.379V6.62A.38.38 0 0 1 5.615 7h-3.23A.38.38 0 0 1 2 6.621V3.38A.38.38 0 0 1 2.384 3z
            M2.384 9h3.231A.38.38 0 0 1 6 9.379V12.6a.38.38 0 0 1-.385.38h-3.23A.38.38 0 0 1 2 12.6V9.38A.38.38 0 0 1 2.384 9z"/>
            <path id="exit" d="M5.04 1.815a1 1 0 0 0-.546.14 7.009 7.009 0 0 0-3.254 7.87 7.006 7.006 0 0 0 6.75 5.19 7.007 7.007 0 0 0 6.766-5.17 7.01 7.01 0 0 0-3.233-7.88 1 1 0 1 0-1.007 1.729 4.991 4.991 0 0 1 2.308 5.627 4.99 4.99 0 0 1-4.832 3.693 4.989 4.989 0 0 1-4.82-3.707 4.992 4.992 0 0 1 2.324-5.62 1 1 0 0 0-.457-1.872z
            M7.984 0A1 1 0 0 0 7 1.015v5a1 1 0 1 0 2 0v-5A1 1 0 0 0 7.984.001z"/>
            <path id="enter" d="M3.707 5.293L2.293 6.707 8 12.414l5.707-5.707-1.414-1.414L8 9.586z M13 6V5h1v1zM2 6V5h1v1z M2 6c0-.554.446-1 1-1s1 .446 1 1-.446 1-1 1-1-.446-1-1zM12 6c0-.554.446-1 1-1s1 .446 1 1-.446 1-1 1-1-.446-1-1z"/>
            <path id="tag" d="M5.525 0C4.433 0 3.61.68 3.295 1.295c-.314.615-.3 1.205-.3 1.205v13.117l5-2.5 5 2.5V11h-2v1.383l-3-1.5-3 1.5V2.5s.013-.16.081-.295c.069-.134.01-.205.45-.205h4.968c.417 0 .35.06.418.197s.082.303.082.303V3h2v-.5s.014-.584-.293-1.197C12.394.689 11.577 0 10.494 0z M11 4v2H9v2h2v2h2V8h2V6h-2V4z"/>
            <path id="good" d="M3.5 1A2.506 2.506 0 0 0 1 3.5v9C1 13.876 2.124 15 3.5 15h9c1.376 0 2.5-1.124 2.5-2.5v-9C15 2.124 13.876 1 12.5 1zm0 1h9c.84 0 1.5.66 1.5 1.5v9c0 .84-.66 1.5-1.5 1.5h-9c-.84 0-1.5-.66-1.5-1.5v-9C2 2.66 2.66 2 3.5 2z M14.5 2.5l-6 6-2-2-2 2 4 4 6-6z"/>
            <path id="bad" d="M4 1C2.338 1 1 2.338 1 4v6c0 1.662 1.338 3 3 3h8c1.662 0 3-1.338 3-3V4c0-1.662-1.338-3-3-3zm2 3c.558 0 1.031.473 1.031 1.031V6c0 .558-.473 1-1.031 1-.558 0-1-.442-1-1v-.969C5 4.473 5.442 4 6 4zm4 0c.558 0 1 .473 1 1.031V6c0 .558-.442 1-1 1s-1-.442-1-1v-.969C9 4.473 9.442 4 10 4zM8 8.031c3.256 0 5 .874 5 1.406v.5c-.997-.636-4.016-.906-5-.906s-3.805-.062-5 .906v-.5c0-.68 1.744-1.406 5-1.406zM8 14c-5 0-5 1-5 1 0 1 1 1 1 1h8c1 0 1-1 1-1s0-1-5-1z"/>
            <path id="add_ev" d="M9 12v2h6v-2h-5z M3 1a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1h4a1 1 0 1 0 0-2H4V3h5.586L12 5.414V8a1 1 0 1 0 2 0V5a1 1 0 0 0-.293-.707l-3-3A1 1 0 0 0 10 1z M11 10v6h2v-6z"/>
        </defs>
    </svg>
    <?php
        require_once("ephemeride.php");
        $base_path = realpath('.');
        if (!is_dir("$base_path/db")) { mkdir("$base_path/db",0770); }
        if (isset($_POST['name']) and isset($_POST['passwd'])) {
            $name = $_POST['name'];
            $passwd = $_POST['passwd'];
            $connected = hash('sha256', $name.$passwd);
            $filename = $base_path.'/db/'.$connected.'.db';
            if (file_exists($filename)) {
                $_SESSION['connected'] = TRUE;
                $_SESSION['base'] = $filename;
                $eph = new ephemeride($filename);
            }
            else {
                if ((count(scandir($base_path.'/db')) < 3) or ((isset($_POST['cde']) and $_POST['cde']=="0000"))) {
                    $eph = new ephemeride($filename);
                    if ($eph->init_table()) {
                        $_SESSION['connected'] = TRUE;
                        $_SESSION['base'] = $filename;
                        $eph->new_log("Utilistateur enregistré", 0);
                    }
                }
            }
        }
?>
<header>
        <h1>Test sqlite v3</h1><?php
        if (isset($_POST['action']))  {
            $_SESSION['connected'] = FALSE;
            $_SESSION = array();
        }
        if (isset($_SESSION['connected'])) {
        $eph = new ephemeride($_SESSION['base']);
        echo <<<EOF
        <nav>
            <form id="nav_form" method="post">
                <ul id=nav>
                    <li hx-get="./index.php" hx-target="#page"><svg class=svg_nav viewBox="0 0 16 16"><use xlink:href="#svg_home"/></svg><pre class=tx>Accueil</pre></li>
                    <li hx-post="htmx/find.php" hx-target="#main"><svg class=svg_nav viewBox="0 0 15 15"><use xlink:href="#search"/></svg><pre class=tx>Rechercher</pre></li>
                    <li hx-post="htmx/add.php" hx-target="#main"><svg class=svg_nav viewBox="0 0 15 15"><use xlink:href="#add"/></svg><pre class=tx>Ajouter</pre></li>
                    <li hx-post="htmx/category.php" hx-target="#main"><svg class=svg_nav viewBox="0 0 15 15"><use xlink:href="#categories"/></svg><pre>Catégories</pre></li>
                    <li hx-post="" hx-target="#page"><input type="hidden" name=action value="logout"><svg class=svg_nav viewBox="0 0 15 15"><use xlink:href="#exit"/></svg><pre>Quiter</pre></li>
                </ul>
            </form>
        </nav>        
EOF;
                // Ajout d'une catégorie
                if (!empty($_POST['new_cat'])) {
                    $eph->new_cat($_POST['new_cat'],$_POST['sub_cat']);
                    // $eph->print_log();
                }
                // Ajout d'une entrée événement
                if (!empty($_POST['date']) and !empty($_POST['n_desc'])) {
                    $eph->new_ev($_POST['date'], $_POST['type'], $_POST['sub_cat'], $_POST['n_desc']);
                    // $eph->print_log();
                }
            }
        ?>       
    </header>
    <main id='main'>
        <?php
            if (!isset($_SESSION['connected'])) {
echo <<<EOF
<h2>Identification</h2>
        <form method="post">
            <ul class='mono-col'>
                <li><input type="text" class="form-control" name="name" placeholder="Nom" autofocus></li>
                <li><input type="password" class="form-control" name="passwd" placeholder="Mot de passe"></li>
EOF;
if (count(scandir($base_path.'/db')) > 2) {
echo <<<EOF
                <li id='cde'><input id='a' type="text" class="form-control" name="cde" placeholder="Code"></li>
                <li id='lien' onclick='document.getElementById("cde").style.display="flex";document.getElementById("a").focus();document.getElementById("lien").style.display="none";document.getElementById("sub").value="Créer l&#39;utilisateur";'>Créer l'utilisateur</li>
EOF;
}
echo <<<EOF

                <li><input type="submit" id="sub" value="Connextion"></li>
            </ul>
        </form>
EOF;

            }
            else {
                // Recherche d'évenement par catégorie
                if (!empty($_POST['find_cat']) and ($_POST['find_cat'] != "")) {
                    $eph->find_by_cat(intval($_POST['find_cat']),$_POST['debut_r'],$_POST['fin_r']);
                }

                // Recherche d'évenement par tags
                if (!empty($_POST['find_tag']) and ($_POST['find_tag'] != "")) {
                    $eph->find_by_tag(intval($_POST['find_tag']),$_POST['debut_r'],$_POST['fin_r']);
                }
                
                // Affichage des anniversaires
                $eph->liste_birthday();
                
                // Affichage des prochains événements
                $eph->liste_next_ev();
            }
            $eph->print_log();
        ?>
    
    </main>
    <footer>
        <?php echo $ephVers; ?>

    </footer>
  </body>
</html>
