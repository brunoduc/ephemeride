<?php
class ephemeride extends SQLite3 {

public $connected;
public $name;
public $password;
public $db;

private $log="";
private $debug=FALSE;

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

private function print_debug(string $message) {
    if ($this->debug) { echo "<br>$message"; }
}

public function init_table() {
    $res = TRUE;
    $commands = ['
    CREATE TABLE IF NOT EXISTS tags (
        tag_id   INTEGER PRIMARY KEY AUTOINCREMENT,
        name     TEXT NOT NULL)
        ','
    CREATE TABLE IF NOT EXISTS category (
        category_id INTEGER PRIMARY KEY AUTOINCREMENT,
        name CHAR(80) NOT NULL,
        parent INTEGER NULL)
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

public function liste_birthday() {
    try {
        $sql_query = "SELECT item_id, strftime('%d %m %Y', date) as date, name FROM items WHERE 
        (category_id=1) AND (
        (strftime('%d', `date`) >= strftime('%d', 'now')) AND
        (strftime('%m', `date`) = strftime('%m', 'now')) OR (
        (strftime('%d', `date`) < strftime('%d', 'now')) AND 
        (strftime('%m', `date`)=strftime('%m', 'now','+1 month'))
        ))";
        $this->print_debug ($sql_query);
        $res=$this->query($sql_query);
        
        $test=$res->fetchArray();
        
        if ($test[item_id]) {
            $res->reset();
            echo "<h3>Prochains anniversaires</h3>";
            echo "<fieldset class=res><ul>";
            while ($row = $res->fetchArray()) {
                echo "<li><span class=date>$row[date]</span>";
                echo "<br>$row[name]\n";
            }
            echo "</ul></fieldset>";
        }
        else {
            echo "<h3>Pas d’anniversaire prochainement</h3>";
        }
    }
    catch(Exception $e) {
        $this->new_log($e->getMessage(), 1);
    }
}

public function liste_next_ev() {
    try {
        $sql_query = "SELECT item_id, strftime('%d %m %Y', date) as date, items.name as name, category.name as cname FROM items, category WHERE
        items.category_id!=1 AND
        items.category_id = category.category_id AND 
        (date > date('now') AND date <= DATE('now', '+1 month')) ORDER BY date";
        $this->print_debug ($sql_query);
        $res=$this->query($sql_query);
        
        $test=$res->fetchArray();
        
        if ($test[item_id]) {
            $res->reset();
            echo "<h3>Prochains événements</h3>";
            echo "<fieldset class=res><ul>";
            while ($row = $res->fetchArray()) {
                echo "<li><span class=date>$row[date]</span> - Catégorie : $row[cname]";
                echo "<br>$row[name]\n";
            }
            echo "</ul></fieldset>";
        }
        else {
            echo "<h3>Pas d'événement prochainement</h3>";
        }
    }
    catch(Exception $e) {
        $this->new_log($e->getMessage(), 1);
    }
}

public function liste_cat($type) {
    echo "IN fonction";
        if ($type) { $sql_query = "SELECT * FROM category WHERE parent = $type ORDER BY name ASC "; }
        else       { $sql_query = "SELECT * FROM category WHERE parent IS NULL ORDER BY name ASC "; }
        $res = $this->query($sql_query);
        while ($row = $res->fetchArray()) {
            echo "<option value='$row[category_id]'>$row[name]</option>\n";
        }
    }
public function list_all_cat() {
//    $sql_query = "SELECT * FROM category ORDER BY name ASC";
    $sql_query = "select a.category_id as ac, a.name as an, b.name as bn from category as a left join category as b on a.parent = b.category_id ORDER BY an ASC";
    $res=$this->query($sql_query);
    while ($row = $res->fetchArray()) {
        echo "<option value='$row[ac]'>$row[an] $row[bn]</option>\n";
    }
}
public function new_cat($cat, $sub_cat) {
        $cat = $this->clean($cat);
        $cat = ucfirst(strtolower($cat));
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

private function reArrayImages($file_post) {
            $file_ary = [];
            $file_keys = array_keys($file_post);
            foreach ($file_post as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $file_ary[$key2][$key] = $value2;
            }
            }
            return $file_ary;
        }

public function new_ev($date, $cat, $sub_cat, $n_desc, $files) {
        $mots = preg_split("/[\s,]+/", $n_desc);
        $tags_array = preg_grep("/^\*/", $mots);
        
        $n_desc = str_replace("*", "", "$n_desc");
        $date         = $this->clean($date);
        $cat          = $this->clean($cat);
        $sub_cat      = $this->clean($sub_cat);
        $n_desc       = $this->clean($n_desc);
        if ($sub_cat != "") { $cat = $sub_cat; }

        $haveFile = count($files['name']);
        
        if ($haveFile) {
            $n_desc=$n_desc."<div class=files><h5>Fichier :</h5>";
            $file_ary = $this->reArrayImages($files);
            foreach ($file_ary as $file) {
                if (!$file['error']) {
                    $tfile=$file['tmp_name'];
                    $nfile=$file['name'];
                    $pfile="files/$nfile";
                    $uploaded = move_uploaded_file($tfile, $pfile);
                    if ($uploaded) {
                        $n_desc=$n_desc."<a href=\"files/$nfile\" target=\"blank\">$nfile</a> ";
                        $this->new_log("Fichier $nfile copié", 0);
                    }
                }
            echo "</div>";
//                 echo '<br>File Name: ' . $file['name'];
//                 echo '<br>File Type: ' . $file['type'];
//                 echo '<br>File Size: ' . $file['size'];
//                 echo '<br>Temp name: ' .$file['tmp_name'];
//                 echo '<br>Erreur: '    .$file['error'];
//                 echo '<br>Taille: '    .$file['size'];
            }
        }

        $this->enableExceptions(true);
        $sql_query = "SELECT COUNT(*) as count FROM items WHERE name= '$n_desc' AND category_id = $cat AND date = '$date'";
        $this->print_debug ($sql_query);
        try { // pour vérifier qu'il n'y aura pas de doublon
            $res=$this->query($sql_query);
            $row = $res->fetchArray();
            if ($row['count'] == 0) { // pas de doublon !
                $sql_query = "INSERT INTO items (name, category_id, date) VALUES ('$n_desc', '$cat', '$date')";
                $this->print_debug ($sql_query);
                try { // On ajoute l'événement
                    $this->query($sql_query);
                    $id_item = $this->lastInsertRowID();
                    $this->new_log("Création de l'événement réussie", 0);
                    $id_tag_array = array();
                    foreach ($tags_array as $tag) {
                        $tag = strtolower(ltrim($tag, "*"));
                        $tag = $this->clean($tag);
                        $sql_query = "SELECT tag_id,name FROM tags WHERE name='$tag'";
                        $this->print_debug ($sql_query);
                        $res = $this->query($sql_query);
                        
                        for ( $nb_res = 0; is_array($res->fetchArray()); ++$nb_res );
                        $this->print_debug ("nb_res = $nb_res");
                        $this->print_debug ("tag = $tag");
                        if ($nb_res) {
                            $row = $res->fetchArray();
                            $id_tag_array['$tag'] = intval($row[0]);
                        }
                        else { 
                            $sql_query = "INSERT INTO tags (name) VALUES ('$tag')";
                            $this->query($sql_query);
                            $id_tag_array["$tag"] = $this->lastInsertRowID();
                            $this->new_log("Création de l'étiquette $tag réussie", 0);
                            $this->print_debug ("On crée l'étiquette $tag");
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
        catch(Exception $e) {
            $this->new_log("$sql_query c ".$e->getMessage(), 1);
        }
    }

public function minmax() {
        try {
            $query = "SELECT strftime('%s',min(date)) min, strftime('%s',max(date)) max, min(date) min_d, max(date) max_d from items";
            $res = $this->query($query);
            if($res==FALSE)
            {
                echo "Error in fetch ".$this->lastErrorMsg();
            }
            else {
                return $res->fetchArray();
            }
        }
        catch(Exception $e) {
            $this->new_log($e->getMessage(), 1);
            $this->print_log();
        }
    }

public function list_all_tag() {
        try {
            $sql_query = "SELECT * FROM tags ORDER BY name ASC ";
            $res=$this->query($sql_query);
            while ($row = $res->fetchArray()) {
                echo "<option value='$row[tag_id]'>$row[name]</option>\n";
            }
        }
        catch(Exception $e) {
            $this->new_log($e->getMessage(), 1);
        }
    }

    public function find_by_tag($find_tag,$debut,$fin) {
        date_default_timezone_set('Europe/Paris');
        $debut=date('Y-m-d',$debut);
        $fin=date('Y-m-d',$fin);
        $sql_query = "select category.name as cname, date, STRFTIME('%d/%m/%Y', date) AS date_f, items.name, tags.name as tags
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
                echo "<fieldset class=res><ul>";
                while ($row = $res->fetchArray()) {
                    echo "<li><span class=date>$row[date_f]</span> - Catégorie : $row[cname]";
                    echo "<br>$row[name]\n";
                }
                echo "</ul></fieldset>";
            }
        }
        catch(Exception $e) {
            $this->new_log($e->getMessage(), 1);
        }
    }
    
    public function find_by_cat($find_cat,$debut,$fin) {
        date_default_timezone_set('Europe/Paris');
        $debut=date('Y-m-d',$debut);
        $fin=date('Y-m-d',$fin);
        
        $sql_query = "SELECT item_id as i, category.name as cname, date, STRFTIME('%d/%m/%Y', date) AS date_f, items.name,  items.item_id
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
                echo "<fieldset class=res><ul>";
                while ($row = $res->fetchArray()) {
                    echo "<li><span class=date>$row[date_f]</span>";
                    
                    $sql_query2 = "select name from tags left join items_has_tags  on tags.tag_id = items_has_tags.tag_id where items_has_tags.item_id= $row[i]";
                    $this->print_debug ("<p>$sql_query2</p>");
                    $res2 = $this->query($sql_query2);
                    echo " Étiquettes : ";
                    while ($row2 = $res2->fetchArray()) {
                        echo "$row2[name] ";
                    }
                    
                    echo "<br>$row[name]</li>\n";
                }
                echo "</ul></fieldset>";
            }
        }
        catch(Exception $e) {
            $this->new_log($e->getMessage(), 1);
        }
    }
    
private function clean($data) {
    $data = trim($data);
    $data = str_replace("'", "&apos;", $data);
    $data = nl2br($data);
    return $data;
    }



// Gestion des logs

public function new_log($log, $type) {
        if ($type!=0) {
            $new_log = "<div class=message><span class=red><svg viewBox='0 0 15 15' class='l_icon'><use xlink:href='#bad'/></svg></span><span class=logs> $log</span></div>\n";
        }
        else {
            $new_log = "<div class=message><span class=green><svg viewBox='0 0 15 15' class='l_icon'><use xlink:href='#good'/></svg></span><span class=logs>$log</span></div>\n";
        }
        $_SESSION['log'] = $_SESSION['log'].$new_log;
    }
    
public function aff_log($log, $type) {
        $this->new_log($log, $type);
        $this->print_log();
    }
    
public function print_log() {
        if (isset($_SESSION['log'])) {
            if ($_SESSION['log']!="") {
                echo $_SESSION['log'];
                $_SESSION['log']="";
            }
        }
    }
    
public function clean_log() {
        $_SESSION['log']!="";
    }

}
?>
