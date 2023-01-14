<?php session_start();

include_once('../ephemeride.php');
$eph = new ephemeride($_SESSION['base']);

$date = date('Y-m-d');
$maxSize = ini_get('post_max_size');
$l_mime ="";
$l_key ="";
foreach ($_SESSION['ALLOWED_FILES'] as $mime) {
    $l_mime = $l_mime." $mime";
}
foreach(array_keys($_SESSION['ALLOWED_FILES']) as $key){
    $l_key = $l_key.$key.',';
}

echo <<<EOF
        <fieldset class=res>
            <form class=form method=post name=add_form  enctype="multipart/form-data">
                <ol>
                <li><label for=date>Date</label>
                    <input id=date type=date value="$date" name=date>
                </li>
                
                <li><label for=cat>Catégorie</label>
                    <select name="type" required hx-get="htmx/select.php" hx-target="#sub_cat" hx-trigger="change">
<option value="">Catégorie obligatoire</option>
EOF;
$eph->liste_cat(0);
echo <<<EOF
                    </select>
                </li>
                
                <li><label for=sub_cat>Sous catégorie</label>
                    <select name='sub_cat' id='sub_cat'>
                        <option value="">&nbsp;</option>
                    </select>
                </li>

                <li><label for=n_desc style="flex-grow:3; width:auto">Description</label>
                    <span class="span_ta" onclick='document.getElementById("n_desc").value = document.getElementById("n_desc").value+"&nbsp;";
                    document.getElementById("n_desc").focus();
                    document.getElementById("n_desc").setSelectionRange(document.getElementById("n_desc").value.length,document.getElementById("n_desc").value.length);
                    '>&amp;nbsp; </span>
                    
                    <svg class="img_ta" onclick='document.getElementById("n_desc").value = document.getElementById("n_desc").value+"*";
                    document.getElementById("n_desc").focus();
                    document.getElementById("n_desc").setSelectionRange(document.getElementById("n_desc").value.length,document.getElementById("n_desc").value.length);
                    '><use xlink:href="img/unique.svg#tag"/></svg>
                    
                    <svg class="img_ta" onclick='document.getElementById("n_desc").value = document.getElementById("n_desc").value+"\\n";
                    document.getElementById("n_desc").focus();
                    document.getElementById("n_desc").setSelectionRange(document.getElementById("n_desc").value.length,document.getElementById("n_desc").value.length);
                    '><use xlink:href="img/unique.svg#enter"/></svg>
                </li>
                <li>
                    <textarea id=n_desc name=n_desc rows=5 cols=40 maxlength =25000 wrap=hard></textarea>
                </li>
                <li>
                    <label for=files class="help" title="Toute erreur liée aux fichiers empécherra l'enregistrement de l'événement.
Le nom du fichier de doit pas déjà existé, et la taille doit être inférieur à $maxSize.">Fichiers à ajouter<p class='p'>types autorisés : $l_mime ($maxSize MAX !)</p></label> 
                    <!-- <input type="checkbox" id="d" name="d" style="width: auto; margin-right: 10px;" >
                    <label for="d"  style="width: auto;"> Autoriser les liens vers des fichiers existants</label> -->
                    <!-- <input type="hidden" name="MAX_FILE_SIZE" value="8388608" /> -->
                    <input type="file" name="files[]" accept="$l_key" id="files" multiple>
                </li>
                </ol>
                <input type="submit" value="Ajouter">
            </form>
        </fieldset>
EOF;



 
