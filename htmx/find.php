<?php session_start();

include_once('../ephemeride.php');
$eph = new ephemeride($_SESSION['base']);

$date = date('Y-m-d');

$res = $eph->minmax();
// var_dump($res);
$min_d = $res['min_d'];
$min_d = date_create($min_d);
$min_d = date_format($min_d, 'd-m-Y');

$max_d = $res['max_d'];
$max_d = date_create($max_d);
$max_d = date_format($max_d, 'd-m-Y');


echo <<<EOF
<script>
function convertTimestamp(timestamp) {
  var d = new Date(timestamp * 1000),	// Convert the passed timestamp to milliseconds
		yyyy = d.getFullYear(),
		mm = ('0' + (d.getMonth() + 1)).slice(-2),	// Months are zero based. Add leading 0.
		dd = ('0' + d.getDate()).slice(-2),			// Add leading 0.
		hh = d.getHours(),
		h = hh,
		min = ('0' + d.getMinutes()).slice(-2),		// Add leading 0.
		ampm = 'AM',
		time;
			
	if (hh > 12) {
		h = hh - 12;
		ampm = 'PM';
	} else if (hh === 12) {
		h = 12;
		ampm = 'PM';
	} else if (hh == 0) {
		h = 12;
	}
	
	// ie: 2013-02-18, 8:35 AM	
	// time = yyyy + '-' + mm + '-' + dd + ', ' + h + ':' + min + ' ' + ampm;
	time = dd + '-' + mm + '-' + yyyy;
		
	return time;
}
</script>
<form class='form' name='find_form' method='post'>
<fieldset class=res>
<legend class=legend>Dates de début et de fin</legend>
    <ol>
    <li>
    <output class=w20 name="debut" id="debut">$min_d</output>
    <input class='w20 s' type="range" name="debut_r" min='$res[min]' max='$res[max]' value='$res[min]'                                                                    
            oninput="this.form.debut.value=convertTimestamp(this.value)">
    <input class='w20 e' type="range" name="fin_r" min='$res[min]' max='$res[max]' value='$res[max]'                                                                    
        oninput="this.form.fin.value=convertTimestamp(this.value)">
    <output class=w20 name="fin" id="fin">$max_d</output>
    </li>
    </ol>
</fieldset>

<fieldset class=res>
    <legend class=legend>Recherche par catégorie/sous catégorie</legend>
        <ol>
            <li><label for=find_cat>Catégorie</label>
                <select name=find_cat id=find_cat onchange="document.forms.find_form.submit();">
                    <option selected> </option>\n
EOF;
$eph->list_use_cat();
echo <<<EOF
                </select>
            </li>
        </ol>
</fieldset>
<fieldset class=res>
    <legend class=legend>Recherche par étiquette</legend>
        <ol>
            <li><label for=find_tag>Étiquette</label>
                <select name=find_tag id=find_tag onchange="document.forms.find_form.submit();">
                    <option selected> </option>\n
EOF;
$eph->list_all_tag();
echo <<<EOF
                </select>
            </li>
            
        </ol>
    </fieldset>
</form>

EOF;
