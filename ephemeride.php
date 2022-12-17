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
            }
        }
        $this->new_log("Création du compte réussie", 0);
    }

public function liste_birthday() {
    try {
        $sql_query = 'SELECT date, DATE_FORMAT(date,"%a %e %M %Y") AS date, name FROM items WHERE (category_id=1) 
        AND ((day(date) >= day(CURDATE()) AND month(date) = month(CURDATE()))
        OR (day(date) < day(CURDATE()) AND month(date) = month(CURDATE())+1 MOD 12)) ORDER BY month(date), day(date) ASC';
        $res=$this->connection->query($sql_query);
        if (!$res->num_rows) { echo "<p>Pas d'anniversaire prochainement</p>"; }
        echo "<ul>";
        while ($row = $res->fetch_assoc()) {
            echo "<li>$row[date] : $row[name]</li>";
        }
        echo "</ul>";
    }
    catch(mysqli_sql_exception $e) {
        $this->new_log($e->getMessage(), 1) ;
    }
}

public function liste_next_ev() {
    try {
        $sql_query = 'SELECT date, DATE_FORMAT(date,"%a %e %M %Y") AS datefr, name FROM items WHERE category_id!=1 
        AND (date >= date(now()) AND date <= DATE_ADD(now(), INTERVAL 30 DAY)) ORDER BY date';
        $res=$this->connection->query($sql_query);
        if (!$res->num_rows) { echo "<p>Pas d'événement prochainement</p>"; }
        echo "<ul>";
        while ($row = $res->fetch_assoc()) {
            echo "<li>$row[datefr] : $row[name]</li>";
        }
        echo "</ul>";
    }
    catch(mysqli_sql_exception $e) {
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
    $sql_query = "SELECT * FROM category ORDER BY name ASC ";
    $res=$this->query($sql_query);
    while ($row = $res->fetchArray()) {
        echo "<option value='$row[category_id]'>$row[name]</option>\n";
    }
}
public function new_cat($cat, $sub_cat) {
        $cat = $this->clean($cat);
        $cat = ucfirst(strtolower($cat));
        $sql_query = "INSERT INTO `category` (`name`, `parent`) VALUES ('$cat', $sub_cat)";
        try {
            $res=$this->query($sql_query);
            $this->new_log("Création de la catégorie $cat réussie", 0);
        }
        catch(Exception $e) {
            $this->new_log($e->getMessage(), 1);
        }
}

public function new_ev($date, $cat, $sub_cat, $n_desc) {
        $mots = preg_split("/[\s,]+/", $n_desc);
        $tags_array = preg_grep("/^\*/", $mots);
        
        $n_desc = str_replace("*", "", "$n_desc");
        $date         = $this->clean($date);
        $cat          = $this->clean($cat);
        $sub_cat      = $this->clean($sub_cat);
        $n_desc       = $this->clean($n_desc);
        if ($sub_cat != "") { $cat = $sub_cat; }
        
        
        $this->enableExceptions(true);
        $sql_query = "SELECT COUNT(*) as count FROM items WHERE name= '$n_desc' AND category_id = $cat AND date = '$date'";
        $this->print_debug ($sql_query);
        try { // pour vérifier qu'il n'y aura pas de doublon
            $res=$this->query($sql_query);
            $row = $res->fetchArray();
            if ($row['count'] == 0) { // pas de doublon !
                $sql_query = "INSERT INTO items (name, category_id, date) VALUES ('$n_desc', '$cat', '$date')";
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
                            $this->new_log($e->getMessage(), 1);
                        }
                    }
                }
                catch(Exception $e) {
                    $this->new_log($e->getMessage(), 1);
                }
            }
        }
        catch(Exception $e) {
            $this->new_log($e->getMessage(), 1);
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
