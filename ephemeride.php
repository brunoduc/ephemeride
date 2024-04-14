<?php
class ephemeride extends SQLite3 {

public bool $connected ;
public string $name;
public string $password;
public string $db;

private bool $debug=FALSE;

function __construct(string $db) {
    $_SESSION['log']="";
     try {
         $this->open($db);
         $command ="PRAGMA foreign_keys = ON";
         $this->exec($command);
         
     } 
     catch (Exception $ex) {
         echo $ex->getMessage();
     }
}

private function print_debug(string $message) :void {
    if ($this->debug) { echo "<br>$message"; }
}

public function init_table() :bool {
    $res = TRUE;
    $commands = ['
    CREATE TABLE IF NOT EXISTS tags (
        tag_id   INTEGER PRIMARY KEY AUTOINCREMENT,
        name     TEXT NOT NULL)
        ','
    CREATE TABLE IF NOT EXISTS category (
        category_id INTEGER PRIMARY KEY AUTOINCREMENT,
        name CHAR(80) NOT NULL,
        parent INTEGER NULL,
        UNIQUE (name,parent))
        ','
    CREATE TABLE IF NOT EXISTS items (
        item_id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category_id INTEGER NOT NULL,
        date TEXT NOT NULL,
        CONSTRAINT fk_Items_category
        FOREIGN KEY (category_id) REFERENCES category (category_id) ON DELETE RESTRICT ON UPDATE NO ACTION)
        ','
    CREATE INDEX IF NOT EXISTS fk_Items_category_idx 
        ON items(category_id)
        ','
    CREATE TABLE IF NOT EXISTS items_has_tags (
        item_id INTEGER NOT NULL,
        tag_id INTEGER NOT NULL,
        PRIMARY KEY (item_id, tag_id),
        FOREIGN KEY (tag_id) REFERENCES tags (tag_id) ON DELETE RESTRICT ON UPDATE NO ACTION)
        ','
    CREATE INDEX IF NOT EXISTS fk_Items_has_tags_tags1_idx ON items_has_tags (tag_id)
        ','
    CREATE INDEX IF NOT EXISTS fk_Items_has_tags_Items1_idx ON items_has_tags (item_id ASC)
        ','
    INSERT INTO category (category_id,name) values (1,\'Anniversaires\');
        '
    ];
        // execute the sql commands to create new tables
        foreach ($commands as $command) {
            if (!$this->exec($command)) {
                echo $command." => Erreur dans l'exécution :<br>".$this->lastErrorMsg()."<br>";
                $res=FALSE;
            }
        }
        return $res;
    }

public function liste_birthday() :void {
    try {
        $sql_query = "SELECT item_id, strftime('%d %m %Y', date) as date_f, name as datas FROM items WHERE 
        (category_id=1) AND (
        (strftime('%d', `date`) >= strftime('%d', 'now')) AND
        (strftime('%m', `date`) = strftime('%m', 'now')) OR (
        (strftime('%d', `date`) < strftime('%d', 'now')) AND 
        (strftime('%m', `date`)=strftime('%m', 'now','+1 month'))
        ))";
        $this->print_debug ($sql_query);
        if ($res=$this->query($sql_query)) {
        
                if ($test=$res->fetchArray()) { 
            
                if ($test['item_id']) {
                    $res->reset();
                    $this->affiche($res, "Prochains anniversaires");
                }
                else {
                    echo "<h3>Pas d’anniversaire prochainement</h3>";
                }
            }
        }
    }
    catch(Exception $e) {
        $this->new_log($e->getMessage(), 1);
    }
}

public function liste_next_ev() :void {
    try {
        $sql_query = "SELECT item_id, strftime('%d %m %Y', date) as date_f, items.name as datas, category.name as categorie FROM items, category WHERE
        items.category_id!=1 AND
        items.category_id = category.category_id AND 
        (date > date('now') AND date <= DATE('now', '+1 month')) ORDER BY date";
        $this->print_debug ($sql_query);
        if ($res=$this->query($sql_query)) {
            if ($test=$res->fetchArray()) {
                if ($test['item_id']) {
                    $res->reset();
                    $this->affiche($res, "Prochains événements");
                }
                else {
                    echo "<h3>Pas d'événement prochainement</h3>";
                }
            }
        }
    }
    catch(Exception $e) {
        $this->new_log($e->getMessage(), 1);
    }
}

public function liste_cat(int $type) :void {
    echo "IN fonction";
        if ($type) { $sql_query = "SELECT * FROM category WHERE parent = $type ORDER BY name ASC "; }
        else       { $sql_query = "SELECT * FROM category WHERE parent IS NULL ORDER BY name ASC "; }
        if ($res = $this->query($sql_query)) {
            while ($row = $res->fetchArray()) {
                echo "<option value='$row[category_id]'>$row[name]</option>\n";
            }
        }
    }
public function list_all_cat() :void {
    $sql_query = "select a.category_id as ac, a.name as an, b.name as bn from category as a left join category as b on a.parent = b.category_id ORDER BY an ASC";
    if ($res=$this->query($sql_query)) {
        while ($row = $res->fetchArray()) {
            echo "<option value='$row[ac]'>$row[an] $row[bn]</option>\n";
        }
    }
}

public function list_use_cat() :void {
    $sql_query = "select DISTINCT a.category_id as ac, a.name as an, b.name as bn from items left join category as a on items.category_id = a.category_id left join category as b on a.parent = b.category_id";
    if ($res=$this->query($sql_query)) {
        $liste = array();
        while ($row = $res->fetchArray()) {
            if (!empty($row['bn'])) {$row['bn'] = $row['bn'].' --> '; } 
            $index = $row['ac'];
            $value = $row['bn'].$row['an'];
            $liste[$index] = "$value";
        }
        asort($liste);
        foreach ($liste as $key => $val) {
            echo "                    <option value='$key'>$val</option>\n";
        }
    }
}

public function new_cat(string $cat, string $sub_cat) :void {
    $cat = $this->clean($cat);
    $cat = ucfirst(strtolower($cat));
    if ($sub_cat=="NULL") {
        $sql_query = "SELECT COUNT(*) AS count from category WHERE name='$cat' AND parent IS NULL";
    }
    else {
        $sql_query = "SELECT COUNT(*) AS count from category WHERE name='$cat' AND parent = '$sub_cat'";
    }
    $this->print_debug ($sql_query);
    if ($res=$this->query($sql_query)) {
        if ($row = $res->fetchArray()) {
            if ($row['count']) {
                $this->new_log("Le couple catégorie/sous catégorie existe déjà !", 1);
            }
            else {
                $sql_query = "INSERT INTO `category` (`name`, `parent`) VALUES ('$cat', $sub_cat)";
                $this->print_debug ($sql_query);
                try {
                    $res=$this->query($sql_query);
                    $this->new_log("Création de la catégorie $cat réussie", 0);
                }
                catch(Exception $e) {
                    $this->new_log($e->getMessage(), 1);
                }
            }
        }
    }
}

/**
 * @param array<string> $file_post
 */
private function reArrayImages(array $file_post) :array {
    $file_ary = [];
    $file_keys = array_keys($file_post);
    foreach ($file_post as $key => $value) {
        foreach ($value as $key2 => $value2) {
            $file_ary[$key2][$key] = $value2;
        }
    }
    /**
    * @param array<array> $file_ary
    */
    return $file_ary;
}

private function get_mime_type(string $filename) :string
{
    $info = finfo_open(FILEINFO_MIME_TYPE);
    if (!$info) {
        return "unknow";
    }
    if ($mime_type = finfo_file($info, $filename)) {
        finfo_close($info);
    }
    else {
        $mime_type ="unknow";
    }
        return $mime_type;
        
}
/**
 * @param array<string> $files
 */
public function new_ev(string $date, string $cat, string $sub_cat, string $n_desc, array $files) :void {

    $mots[] = preg_split("/[\s,]+/", $n_desc);
    $tags_array = array();
    $tags_array = preg_grep("/^\*/", $mots);
    
    $n_desc = str_replace("*", "", "$n_desc");
    $date         = $this->clean($date);
    $cat          = $this->clean($cat);
    $sub_cat      = $this->clean($sub_cat);
    $n_desc       = $this->clean($n_desc);
    if ($sub_cat != "") { $cat = $sub_cat; }
    
    $file_ary = $this->reArrayImages($files);
    $haveError = $file_ary[0]['error'];
    
    $error_tab = array(
        0 => "Pas d'Erreur !",
        1 => "La Taille du fichier envoyé excède la limite autorisé dans php.ini", // NE MARCHE PAS (Pas de message d'erreur, ni aucun enregistrement)
        2 => "La Taille du fichier envoyé excède la limite MAX_FILE_SIZE du formulaire html",
        3 => "Fichier partiellement téléchargé",
        4 => "Pas de fichier téléchargé",
        5 => "Pas de répertoire temporaire",
        6 => "Impossible d'écrire le fichier sur le disque",
        7 => "File upload stopped by extension",
    );
    
    $abort=FALSE;
    
    if (!$haveError) {
        $n_desc=$n_desc."<span class=files>Fichier&nbsp;: ";
        foreach ($file_ary as $file) {
            $haveError = $file['error'];
            if (!$haveError) {
                $tfile=$file['tmp_name'];
                $nfile=$file['name'];
                $pfile="users/".$_SESSION['base_name']."/$nfile";
                
                $mime_type = $this->get_mime_type($tfile);
                                
                require_once("./config.inc.php");
                if (!in_array($mime_type, array_keys($_SESSION['ALLOWED_FILES']))) {
                    $this->new_log("Erreur : Format de fichier $mime_type non autorisé ! : Pas d'enregistrement !",1); 
                    $abort=TRUE;
                }
                
                if (!$abort) {
                    if (!file_exists($pfile))  {
                        $uploaded = move_uploaded_file($tfile, $pfile);
                        if ($uploaded) {
                            $n_desc=$n_desc."<a href=\"users/$_SESSION[base_name]/$nfile\" target=\"blank\">$nfile</a> ";
                            $this->new_log("Fichier $nfile copié", 0);
                        }
                    }
                    else { 
                        $this->new_log("Erreur : le fichier $nfile existait ! : Pas d'enregistrement !",1); 
                        $abort=TRUE;
                    }
                }
            }
            elseif ($haveError != 4) {
                $this->new_log("$error_tab[$haveError] : Pas d'enregistrement !", 1);
                $abort=TRUE;
            }
        }
        $n_desc=$n_desc."</span>";
    }
    elseif ($haveError != 4) {
        $this->new_log("$error_tab[$haveError]", 1);
    }
        
    if (!$abort) {
        $this->enableExceptions(true);
        $n_desc = str_replace("'", "&apos;", $n_desc);
        $sql_query = "SELECT COUNT(*) as count FROM items WHERE name= '$n_desc' AND category_id = $cat AND date = '$date'";
        $this->print_debug ($sql_query);
        try { // pour vérifier qu'il n'y aura pas de doublon
            if ($res=$this->query($sql_query)) {
            if ($row = $res->fetchArray()) {
            if ($row['count'] == 0) { // pas de doublon !
                $sql_query = "INSERT INTO items (name, category_id, date) VALUES ('$n_desc', '$cat', '$date')";
                $this->print_debug ($sql_query);
                try { // On ajoute l'événement
                    $this->query($sql_query);
                    $id_item = $this->lastInsertRowID();
                    $this->new_log("Création de l'événement réussie", 0);
                    $id_tag_array = array();
                 if ($tags_array) {
                    foreach ($tags_array as $tag) {
                        $tag = strtolower(ltrim($tag, "*"));
                        $tag = $this->clean($tag);
                        $tag = rtrim($tag, ';,.!? ');
                        $sql_query = "SELECT tag_id,name FROM tags WHERE name='$tag'";
                        $this->print_debug ($sql_query);
                        $nb_res = FALSE;
                        if ($res = $this->query($sql_query)) {
                            for ( $nb_res = 0; $res->fetchArray(); ++$nb_res );
                            $this->print_debug ("nb_res = $nb_res");
                            $this->print_debug ("tag = $tag");
                            if ($nb_res) {
                                $row = $res->fetchArray();
                                $id_tag_array['$tag'] = intval($row[0]);
                            }
                        }
                        else { 
                            $sql_query = "INSERT INTO tags (name) VALUES ('$tag')";
                            $this->query($sql_query);
                            $id_tag_array["$tag"] = $this->lastInsertRowID();
                            $this->new_log("Création de l'étiquette $tag réussie", 0);
                            $this->print_debug ("On crée l'étiquette $tag");
                        }
                    }
                }
                    foreach ($id_tag_array as $id_tag) {
                        $sql_query = "INSERT INTO items_has_tags (item_id, tag_id) VALUES ($id_item, $id_tag)";
                        $this->print_debug ("On affecte $id_tag à $id_item");
//                        if (!$this->query($sql_query)) { $this->new_log($e->getMessage(), 1); }
                        try {
                            $this->query($sql_query);
                        }
                        catch(Exception $e) {
                            $this->new_log("a ".$e->getMessage(), 1);
                        }
                    }
                }
                catch(Exception $e) {
                    $this->new_log("b ".$e->getMessage(), 1);
                }
            }
        }
        }
        }
        catch(Exception $e) {
            $this->new_log("$sql_query c ".$e->getMessage(), 1);
        }
    }
}

public function minmax() :mixed {
    try {
        $query = "SELECT strftime('%s',min(date)) min, strftime('%s',max(date)) max, min(date) min_d, max(date) max_d from items";
        $res = $this->query($query);
        if($res==FALSE)
        {
            echo "Error in fetch ".$this->lastErrorMsg();
            return FALSE;
        }
        else {
            return $res->fetchArray();
        }
    }
    catch(Exception $e) {
        $this->new_log($e->getMessage(), 1);
        $this->print_log();
        return FALSE;
    }
}

public function list_all_tag() :void {
    try {
        $sql_query = "SELECT * FROM tags ORDER BY name ASC ";
        if ($res=$this->query($sql_query)) {
            while ($row = $res->fetchArray()) {
                echo "                    <option value='$row[tag_id]'>$row[name]</option>\n";
            }
        }
    }
    catch(Exception $e) {
        $this->new_log($e->getMessage(), 1);
    }
}

public function find_by_tag(int $find_tag, int $debut, int $fin) :void {
    $tag_name="";
    try {
        $result = $this->querySingle("select name from tags where tag_id = $find_tag", true);
        if (is_array($result)) {
            $tag_name = $result['name'];
        }
    }
    catch(Exception $e) {
        $this->new_log($e->getMessage(), 1);
    }

    date_default_timezone_set('Europe/Paris');
    if ($debut > $fin) {
        $a = $debut;
        $debut = $fin;
        $fin = $a;
    }
    $debut=date('Y-m-d',$debut);
    $fin=date('Y-m-d',$fin);
    $sql_query = "select category.name as categorie, date, STRFTIME('%d/%m/%Y', date) AS date_f, items.name as datas
    FROM items, items_has_tags i, tags, category
    WHERE items.item_id = i.item_id AND i.tag_id = tags.tag_id and items.category_id = category.category_id 
    AND tags.tag_id = $find_tag
    AND date >= '$debut'
    AND date <= '$fin'
    ORDER BY date DESC
    ";
    
    $this->print_debug ($sql_query);
    
    try {
        $res=$this->query($sql_query);
        if ($res) {
            $this->affiche($res, "Recherche du tag <u>$tag_name</u>");
        }
    }
    catch(Exception $e) {
        $this->new_log($e->getMessage(), 1);
    }
}

private function affiche(SQLite3Result $result, string $titre) :void {
//  date_f | categorie | tags | datas 
// Affiche le titre
echo "<h3>$titre</h3>\n";
$a = FALSE;
    echo "<fieldset class=res>\n<ul>\n";
            while ($row = $result->fetchArray()) {
                if ($a) { echo "<hr>\n"; }
                echo "<li>";
                echo "<span class=date>$row[date_f]</span>";
                if (isset($row['tags'])) {
                    echo "<span class=tags><u>Tags :</u> $row[tags]</span>";
                }
                if (isset($row['categorie'])) {
                    echo "<span class=categories><u>Catégorie</u> : $row[categorie]</span>";
                }
                echo "<p class='datas'>$row[datas]</p></li>\n";
                $a = TRUE;
            }
            echo "</ul>\n</fieldset>\n";
}

public function find_by_cat(int $find_cat, int $debut, int $fin) :void {
    $cat_name = "";
    try {
        $result = $this->querySingle("select b.name as bn, a.name as an from category as a left join category as b on a.parent = b.category_id where a.category_id = $find_cat order by bn", true);
        if (is_array($result)) {
            if ($result['bn'] != "") { 
                $cat_name = $result['bn']." --> "; 
            }
            $cat_name = $cat_name.$result['an'];
        }
    }
    catch(Exception $e) {
        $this->new_log($e->getMessage(), 1);
    }
    
    date_default_timezone_set('Europe/Paris');
    if ($debut > $fin) {
        $a = $debut;
        $debut = $fin;
        $fin = $a;
    }
    $debut=date('Y-m-d',$debut);
    $fin=date('Y-m-d',$fin);
    
    $sql_query = "SELECT item_id as i, category.name, date, STRFTIME('%d/%m/%Y', date) AS date_f, items.name as datas,  items.item_id
        FROM category, items
        WHERE  items.category_id = category.category_id 
        AND (items.category_id=$find_cat 
        OR items.category_id IN (SELECT category_id FROM category WHERE parent = $find_cat))
        AND date BETWEEN '$debut' AND '$fin'
        ORDER by date DESC";
        
    $this->print_debug ($sql_query);
    
    try {
        $res=$this->query($sql_query);
        if ($res) {
            $this->affiche($res, "Catégorie <u>$cat_name</u>");
        }
    }
    catch(Exception $e) {
        $this->new_log($e->getMessage(), 1);
    }
}

/**
 * @param array<string> $files
 */
public function restore(array $files) :void {
    $file_ary = $this->reArrayImages($files);
    
    require_once("./config.inc.php");
    
    foreach ($file_ary as $file) {
        $haveError = $file['error'];
        if (!$haveError) {
            $tfile=$file['tmp_name'];
            $nfile=$file['name'];
            $pfile="users/".$_SESSION['base_name']."/$nfile";
            
            $mime_type = $this->get_mime_type($tfile);
            
            if (!in_array($mime_type, array_keys($_SESSION['ALLOWED_FILES'])) and $mime_type != "application/x-sqlite3") {
                $this->new_log("Erreur : Format de fichier $mime_type non autorisé ! : Pas d'enregistrement !",1); 
                $abort=TRUE;
            }
            else {
                if ($nfile == "base.sqlite3") {
                    $date =  date("Y-m-d H:i:s");
                    rename($pfile, $pfile." ".$date." ~");
                }
                $uploaded = move_uploaded_file($tfile, $pfile);
                if ($uploaded) {
                    $this->new_log("Fichier $nfile de type $mime_type copié", 0);
                }
            }
        }
    }

}
    
private function clean(string $data) :string {
    $data = trim($data);
    $data = str_replace("'", "&apos;", $data);
    $data = nl2br($data);
    return $data;
}



// Gestion des logs

public function new_log(string $log, int $type) :void {
    if ($type!=0) {
        $new_log = "<p><svg class=img_ta><use xlink:href=\"css/icones.svg#warn\" /></svg><span class=logs> $log</span></p>\n";
    }
    else {
        $new_log = "<p><svg class=img_ta><use xlink:href=\"css/icones.svg#ok\" /></svg><span class=logs>$log</span></p>\n";
    }
    $_SESSION['log'] = $_SESSION['log'].$new_log;
}
    
public function aff_log(string $log, int $type) :void {
    $this->new_log($log, $type);
    $this->print_log();
}
    
public function print_log() : void {
    if (isset($_SESSION['log'])) {
        if ($_SESSION['log']!="") {
            echo "<h3>Messages</h3>";
            echo "<div class=message>".$_SESSION['log']."</div>";
            $_SESSION['log']="";
        }
    }
}
    
public function clean_log() : void {
    $_SESSION['log']!="";
}

}
?>
