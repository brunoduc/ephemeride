<?php session_start();

include_once('../ephemeride.php');
$eph = new ephemeride($_SESSION['base']);

$date = date('Y-m-d');

echo <<<EOF
        <script src="htmx.min.js"></script>
        <fieldset class=res>
            <form class=form method=post name=add_form>
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
                    <span class="span_ta" viewBox="0 0 15 15" onclick='document.getElementById("n_desc").value = document.getElementById("n_desc").value+"&nbsp;";
                    document.getElementById("n_desc").focus();
                    document.getElementById("n_desc").setSelectionRange(document.getElementById("n_desc").value.length,document.getElementById("n_desc").value.length);
                    '>&amp;nbsp;</span>
                    <svg class="img_ta" viewBox="0 0 15 15" onclick='document.getElementById("n_desc").value = document.getElementById("n_desc").value+"<br>\\n";
                    document.getElementById("n_desc").focus();
                    document.getElementById("n_desc").setSelectionRange(document.getElementById("n_desc").value.length,document.getElementById("n_desc").value.length);
                    '><use xlink:href="#enter"/></svg>
                    <svg class="img_ta" viewBox="0 0 15 15" onclick='document.getElementById("n_desc").value = document.getElementById("n_desc").value+"*";
                    document.getElementById("n_desc").focus();
                    document.getElementById("n_desc").setSelectionRange(document.getElementById("n_desc").value.length,document.getElementById("n_desc").value.length);
                    '><use xlink:href="#tag"/></svg>
                </li>
                <li>
                    <textarea id=n_desc name=n_desc rows=5 cols=40 maxlength =25000 wrap=hard></textarea>
                </li>
                </ol>
                <input type="submit" value="Ajouter">
            </form>
        </fieldset>
EOF;



 
