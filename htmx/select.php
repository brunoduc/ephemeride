<?php session_start();
    $type = $_GET['type'];
    include_once('../ephemeride.php');
    $eph = new ephemeride($_SESSION['base']);    
    $sql_query = "";
    echo "<!-- type = $type -->";
    if (is_numeric($type)) { $sql_query = "SELECT * FROM category WHERE parent = $type ORDER BY name ASC "; }
        echo "<!-- sql = $sql_query -->";
    if ($res = $eph->query($sql_query)) {
    while ($row = $res->fetchArray()) {
        echo "<option value='$row[category_id]'>$row[name]</option>\n";
    }
    echo "<option></option>";
    }
?>
