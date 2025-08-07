<?php session_start(); require_once("config.inc.php"); ?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <title><?php echo TITRE ?></title>
    <script src="htmx/htmx2.min.js"></script>
  </head>
  <body id='page'>
    <?php
        require_once("ephemeride.php");
        $base_path = realpath('.');
        $nb_users = count(scandir($base_path.'/users')) - 3;
        if ( ! is_writable(dirname("$base_path/users/."))) {
            $msg = dirname("$base_path/users/.") . ' must writable!!!';
        }
        else {
        
        if (isset($_POST['name']) and isset($_POST['passwd'])) {
            $name = $_POST['name'];
            $passwd = $_POST['passwd'];
            $connected = hash('sha256', $name.$passwd);
            $filename = $base_path.'/users/'.$connected.'/base.sqlite3';
            $userDir = $base_path.'/users/'.$connected;
            if (file_exists($filename)) {
                $_SESSION['connected'] = TRUE;
                $_SESSION['userDir'] = $userDir;
                $_SESSION['base'] = $filename;
                $_SESSION['base_name'] = $connected;
                $_SESSION['user'] = $name;
                $eph = new ephemeride($filename);
            }
            else {
                if (($nb_users < 1) or ((isset($_POST['cde']) and $_POST['cde']==ADD_USER_CODE))) {
                    mkdir("$base_path/users/$connected", 0700);
                    $eph = new ephemeride($filename);
                    if ($eph->init_table()) {
                        $_SESSION['connected'] = TRUE;
                        $_SESSION['base'] = $filename;
                        $_SESSION['base_name'] = $connected;
                        $_SESSION['user'] = $name;
                        $eph->new_log("Utilisateur enregistré", 0);
                    }
                }
            }
        }
        
        elseif (isset($_SESSION['connected']) AND $_SESSION['connected']) {
            $eph = new ephemeride($_SESSION['base']);        
        }
        
        }
?>
<header>
        <div id="logo"><svg><use xlink:href="css/icones.svg#logo" /></svg><h1><?php echo TITRE ?></h1></div>
        <?php
        if (isset($msg)) { echo "<h2>$msg</h2>"; }
        if (isset($_POST['exit']))  {
            $_SESSION['connected'] = FALSE;
            $_SESSION = array();
        }
        if (isset($_SESSION['connected'])) {
        echo <<<EOF
<nav>
            <form id="nav_form" method="post">
                <ul id=nav>
                    <li hx-get="./index.php" hx-target="#page" hx-push-url="./"><svg><use xlink:href="css/icones.svg#home" /></svg><span class=tx>Accueil</span></li>
                    <li hx-post="htmx/find.php" hx-target="#main" hx-push-url="./"><svg><use xlink:href="css/icones.svg#find" /></svg><span class=tx>Rechercher</span></li>
                    <li hx-post="htmx/add.php" hx-target="#main" hx-push-url="./"><svg><use xlink:href="css/icones.svg#add" /></svg><span class=tx>Ajouter</span></li>
                    <li hx-post="htmx/category.php" hx-target="#main" hx-push-url="./"><svg><use xlink:href="css/icones.svg#cat" /></svg><span class=tx>Catégories</span></li>
                    <li hx-post="" hx-target="#page"><input type="hidden" name=exit value="logout"><svg><use xlink:href="css/icones.svg#exit" /></svg><span class=tx>
EOF;

echo "$_SESSION[user]";

echo <<<EOF
</span></li>
                </ul>
            </form>
        </nav>        
EOF;
                // Ajout d'une catégorie
                if (!empty($_POST['new_cat']) and isset($eph)) {
                    $eph->new_cat($_POST['new_cat'],$_POST['sub_cat']);
                    // $eph->print_log();
                }
                // Ajout d'une entrée événement
                if (!empty($_POST['date']) and !empty($_POST['n_desc']) and isset($eph)) {
                    $eph->new_ev($_POST['date'], $_POST['type'], $_POST['sub_cat'], $_POST['n_desc'], $_FILES['files']);
                    // $eph->print_log();
                }
                // Restauration de fichiers
                
                if (isset($_FILES['restore']) and isset($eph)) {
                    $eph->restore($_FILES['restore']);
                }
                
            }
        ?>       
    </header>
    <main id='main'>
        <?php
            if (!isset($_SESSION['connected'])) {
echo <<<EOF
<h2 id="titre-h2">Identification</h2>
        <form method="post">
            <ul class='mono-col'>
                <li><input type="text" class="form-control" name="name" placeholder="Nom" autofocus></li>
                <li><input type="password" class="form-control" name="passwd" placeholder="Mot de passe"></li>
                
EOF;
if ($nb_users > 0) {
echo <<<EOF
\n                <li id='cde'><input id='a' type="text" class="form-control" name="cde" placeholder="Code"></li>
EOF;
}
echo <<<EOF

                <li><input type="submit" id="sub" value="Connextion"></li>
            </ul>
        </form>
EOF;

            }
            elseif (isset($eph)) {
                $eph->print_log();
                
                // Recherche d'évenement par catégorie
                if (!empty($_POST['find_cat']) and ($_POST['find_cat'] != "")) {
                    $eph->find_by_cat(intval($_POST['find_cat']),$_POST['debut_r'],$_POST['fin_r']);
                }

                // Recherche d'évenement par tags
                elseif (!empty($_POST['find_tag']) and ($_POST['find_tag'] != "")) {
                    $eph->find_by_tag(intval($_POST['find_tag']),$_POST['debut_r'],$_POST['fin_r']);
                }
                
                // Rien de demandé alors 
                else {
                    // Affichage des anniversaires
                    $eph->liste_birthday();
                    
                    // Affichage des prochains événements
                    $eph->liste_next_ev();
                }
            }
        ?>
    
    </main>
    <footer>
        <span>
        <?php
        if (isset($_SESSION['connected'])) {
            $json = trim(shell_exec('curl -s "https://api.github.com/repos/brunoduc/ephemeride/releases/latest"  | jq -r ".tag_name"'));
            if ($json!=EPH_VERS) {
                $file_name = "$json.zip";
                if (!file_exists($file_name)) {
                    // Initialize a file URL to the variable
                    $url = "https://github.com/brunoduc/ephemeride/archive/refs/tags/$file_name";

                    // Use file_get_contents() function to get the file from url and use file_put_contents() function to
                    // save the file by using base name

                    if (file_put_contents("$file_name", file_get_contents($url))){
                        echo "Mise à jour ".EPH_VERS." vers ".$json." disponible. ";
                    }
                    else{
                        echo "Echec du téléchargement de la MAJ. ";
                    }
                }
                if (file_exists($file_name)) {
    echo <<<EOF
    <button class="maj" hx-get="htmx/install-maj.php" hx-vals='{"version": "$json"}' hx-swap="outerHTML">
    &nbsp;Mettre à jour&nbsp;
    </button>
    EOF;
                }
            }
            else { echo "Version ".EPH_VERS; }
        }
        ?></span>
        <ul class="footer">
        <?php
            if (isset($_SESSION['connected'])) {
                echo '<li><a title="backup du compte" href="htmx/backup.php"><svg><use xlink:href="css/icones.svg#backup" /></svg></a></li>'; 
                echo '<li><a title="backup de la base" href="users/'.$_SESSION['base_name'].'/base.sqlite3"><svg><use xlink:href="css/icones.svg#base" /></svg></a></li>'; 
                echo '<li hx-post="htmx/restore.php" hx-target="#main" title="Restaurer"><svg><use xlink:href="css/icones.svg#restbase" /></svg></li>';
            }
            else {
                if ($nb_users > 0) {
echo <<<EOF
<li style="padding:0px;" id='lien' 
                onclick='document.getElementById("titre-h2").textContent = "Nouvel utilisateur";
                        document.getElementById("cde").style.display="flex";
                        document.getElementById("a").focus();
                        document.getElementById("lien").style.display="none";
                        document.getElementById("sub").value="Créer l&#39;utilisateur";'
                title="Ajouter un utilisateur">
                <svg><use xlink:href="css/icones.svg#user"/></svg>
            </li>
EOF;
                }
            }
            ?>
            
        </ul>
     </footer>
  </body>
</html>
