<?php session_start();
    $type = $_GET['type'];
    include_once('../ephemeride.php');
    $eph = new ephemeride($_SESSION['base']);    
    $sql_query = "";
    echo "<!-- type = $type -->";
    if (is_numeric($type)) { $sql_query = "select a.category_id as ac, a.name as an, b.name as bn from category as a left join category as b on a.parent = b.category_id  WHERE a.parent = $type ORDER BY an ASC "; }
        echo "<!-- sql = $sql_query -->";
    if ($res = $eph->query($sql_query)) {
    while ($row = $res->fetchArray()) {
//        echo "<option value='$row[ac]'>$row[an] $row[bn]</option>\n";
        echo "<option value='$row[ac]'>$row[an]</option>\n";
    }
    echo "<option></option>";
    }
?>
