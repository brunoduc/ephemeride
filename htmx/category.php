<?php session_start();

include_once('../ephemeride.php');
$eph = new ephemeride($_SESSION['base']);

echo <<<EOF
<fieldset class=res>
    <form class=form name=nc method=post>
        <ol>
        <li><label for=new_cat>Nouvelle catégorie</label>
        <input required id=new_cat type=text value="" name=new_cat>
        </li>
        <li>
        <label for=sub_cat>Sous catégorie de :</label>
        <select name=sub_cat id=sub_cat>
            <option value="NULL">* Catégorie parente *</option>
EOF;
$eph->list_all_cat();
echo <<<EOF
         </select>
        </li>
        </ol>
        <input type="submit" value="Ajouter">
    </form>
</fieldset>

EOF; 
