<?php session_start();

include_once('../ephemeride.php');
$eph = new ephemeride($_SESSION['base']);

$maxSize = ini_get('post_max_size');

$l_mime ="";
$l_key ="";
foreach ($_SESSION['ALLOWED_FILES'] as $mime) {
    $l_mime = $l_mime." $mime";
}
foreach(array_keys($_SESSION['ALLOWED_FILES']) as $key){
    $l_key = $l_key.$key.',';
}
$l_key = $l_key.'application/x-sqlite3';

echo <<<EOF
        <fieldset class=res>
            <form class=form method=post name=add_form  enctype="multipart/form-data">
                <label for=files class="help" title="La taille doit être inférieur à $maxSize.">Fichiers à restaurer ($maxSize MAX !)</label>
                <p>Veuillez indiquer le/les fichiers à restaurer.<br>
                Les types de fichiers autorisés sont $l_mime et le fichier de la base "base.sqlite3".</p>
                <!-- <input type="hidden" name="MAX_FILE_SIZE" value="8388608" /> -->
                <input type="file" name="restore[]" accept="$l_key" id="files" multiple>
                <input type="submit" value="Restorer ces fichiers">
            </form>
        </fieldset>
EOF;



 
