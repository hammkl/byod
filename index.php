<?php
/** This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version, see http://www.gnu.org/licenses/
alternative liceses can be attained by
Klaus Hammermueller, Open Learning Association klaus@o-le.org **/

	
	//Looking into the header
	$ip = $_SERVER['REMOTE_ADDR'];
	$uriParts = explode("?", $_SERVER['REQUEST_URI']);
	$postReq = ($_SERVER['REQUEST_METHOD'] == "POST");
	
	// POST
	if ($postReq) {
		$req_param = $_POST;
	// GET
	} else {
		$req_param = $_GET;
	}
	
	//default language
	$lang = "de";
	$nrColor = "black";
	$nrBackground = $req_param['K'];
	$nrCompetency = "H".implode(",H",explode(",",$req_param['H']));
	$lCompetency = "I" . $req_param['I'];
	//book
	$book = $req_param['book'];
	$noBookBackground = '';
	$noBook = strlen($book) <1;
	if ($noBook) {
		$book = "BL";
		$noBookBackground = "background-color:grey;";
	} elseif (substr($book, 0, 2) === "GD") {
		$levelColors = array("r" => "red", "o" => "orange", "y" => "yellow", "g" => "green");
		$nrColor = $levelColors[$req_param['K']];
		$nrBackground = $req_param['H'];
		$nrCompetency = '<b style="font-size:200%;font-stretch:expanded;color:'.$levelColors[$req_param['H']].';">i</b>';
		$lCompetency = $req_param['I'];
	} 
	
	//tag
	$tag = $req_param['enc'];
	$quicklink = $req_param['quicklink'];
	//encode text
	$heading = rawurldecode($req_param['heading']);
	$hint = ($req_param['hint']);
	//fieldId(s)
	$ffId = intval($req_param['fId']) - 1;
	$fCount = intval($req_param['fCount']);
	$logId = 0;
	
	// see if we have subnumbers - which and how manny
	$subLabels = explode(",",$req_param['sub']);
	$subCount = count($subLabels);
	$subInterval = 0;
	$startSubNr = 0;
	$insertSubNr = ($subCount > 0);
	if ($insertSubNr) {
		if (strlen($subLabels[0]) < 1)		// check if we really have sublabels
			$subCount = 0;
		elseif ($subCount == 1) {
			if (ctype_lower($subLabels[0])) // a, b, c, ...
				$subCount = ord($subLabels[0]) - ord("a") + 1;
			else 							// A, B, C, ...	
				$subCount = ord($subLabels[0]) - ord("A") + 1;
			$startSubNr = ord($subLabels[0]) - $subCount - 1;
			for ($i = 0; $i < $subCount; $i++)
				$subLabels[$i] = chr($startSubNr + $i + 2);
			$subInterval = (int) $fCount / $subCount;			
		}
	}	

	//session timestamp
	$sTS = microtime(true);
	$checkAnswer = "false";
?>
<html>
<head>
<!-- This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version, see http://www.gnu.org/licenses/
Klaus Hammermueller, Open Learning Association klaus@o-le.org -->

	<link rel="shortcut icon" href="img2/favicon.ico" type="image/x-icon" />
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<!--  changed: Labels -->
	<title>Beispiel App</title>
	   
	<link rel="stylesheet" href="css/jquery-ui.min.css" />
	<link href="css/jquery.tagit.css" rel="stylesheet" type="text/css"> 
	<link href="css/tagit.ui-zendesk.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="css/flag-icon.min.css">
	<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
	
	<script src="js/jquery.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/cordova-2.2.0.js"></script>
	<script src="js/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script> 
    <script src="js/jquery.ddslick.min.js" type="text/javascript" ></script>
    
	<script src="js/tag-it.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jsaes.js"></script>		 <!-- AES encryption -->
	<script src="js/bytescoutpdf.js"></script> <!-- pdf creation -->
	<script src="js/jquery.qrcode.min.js"></script> <!-- qr-code creation -->
	<script src="js/lip.js"></script> <!-- our stuff, add last -->	
     
<style type="text/css">
  * {
    padding: 0;
    margin: 0;
  }
  .fit { /* set relative picture size */
    max-width: 100%;
    max-height: 100%;
  }
  .center {
    display: block;
    margin: auto;
  }
  
p {font-size: 16px; font-size: 3vw; font-family: sans-serif; }
  
/* Allow Font Awesome Icons in lieu of jQuery UI and only apply when using a FA icon */
.ui-icon[class*=" fa-"] {
    /* Remove the jQuery UI Icon */
    background: none repeat scroll 0 0 transparent;
    /* Remove the jQuery UI Text Indent */
    text-indent: 0; 
    /* Bump it up - jQuery UI is -8px */
    margin-top: -0.5em;
}

/* Allow use of icon-large to be properly aligned */
.ui-icon.icon-large {
    margin-top: -0.75em;
}

.ui-button-icon-only .ui-icon[class*=" fa-"] {
    /* Bump it - jQuery UI is -8px */
    margin-left: -7px;
}
    
/* font awsome color */
.icon-cog {
  color: grey;
}
.icon-ok {
  color: green;
}
.icon-white {
  color: white;
}
.icon-nok {
  color: red;
}
.icon-gold {
  color: gold;
}
.fa-cog {
  color: grey;
}


#tableContainer {
    vertical-align: middle;
    display: table;
    height: 100%;
}
#menu img{
        width:100%; height:auto;
        display:block;
}
#menu table {
    margin: 0 auto;
}

table {
	border-style: none;
	border-color: white;
	border-collapse: separate;
}
table th {
	border-style: none;
	border-color: white;
}
table td {
	border-style: none;
	border-color: white;
}

table.invisible { 
	cellspacing: 0; 
	cellpadding: 0; 
	border: 0;
	frame: void;
	width: 100%;
	border-color: white;
}

.percent-complete-bar {
  display: inline-block;
  height: 6px;
  -moz-border-radius: 3px;
  -webkit-border-radius: 3px;
}

.cell-title {
	font-weight: bold;
}

.cell-effort-driven {
	text-align: center;
}

#fotoImg {
	width: 98%;		
}

</style>
 </head>
<body>
<!--  changed: Labels -->
<meta itemprop="name" content="Mein Schulbuch">
<script language="JavaScript" type="text/javascript" src="https://vhss.oddcast.com/vocalware_embed_functions.php?acc=5592457&js=1"></script>
<script language="JavaScript" type="text/javascript">AC_Vocalware_Embed(5592457,300, 400,'',1,1, 2472414, 0,1,0,'2101383df75a4a9361d8eb0d1ae392b1',9);</script>

<table style="align:center;width=100%"><tr><td style="align:center;width=100%">
	<table id="menue" class="invisible" style="max-width:800px;">
	  <tr><td colspan="3" background="img2/<?= $book ?>.png" style="background-repeat:no-repeat;height:100%;width:100%;background-position:center top;max-width:800px;max-height:287px;<?= $noBookBackground ?>">    
	    <table class="invisible">
	    	<tr><td rowspan="3"><img src="img2/spacer287.png" /></td><td colspan="3"><img class="fit" src="img2/spacer800.png" /></td></tr>
	    	<tr><td width="6%" valign="top"><p>&nbsp;<label id="inputLabel_QL" for="inputTags_QL" style="color:white">Quicklink:</label>&nbsp;</p></td>
			<td width="24%" valign="top"><p valign="top"><input type="text" name="entry_QL" id="inputTags_QL" style="width:100%" value="" /></p></td>
			<td width="70%"><p>&nbsp;</p></td>
			</tr><tr><td colspan="3"><p>&nbsp;</p></td></tr>
	  	</table></td></tr>
<?php // NO BOOK SELECTED
if (!$noBook) {
?>	  	
  	  <tr><td colspan="3">&nbsp;</td></tr>
  	  <tr>
  	    <td width="12%" valign="top"><table class="invisible"><tr>
  	      <td width="20px" valign="top"><img class="fit" src="img2/webb<?= $req_param['w'] ?>.png" valign="top" /></td>
  	      <td width="120px" rowspan="2" valign="top" align="center" background="img2/<?= $req_param['book'] . $nrBackground ?>.jpg" style="background-repeat:no-repeat;height:100%;width:auto;background-position:center top;">
  		  	<table class="invisible">
	    		<tr><td rowspan="3"><img class="center fit" src="img2/spacer157.png" /></td><td><img src="img2/spacer.png" /></td></tr>
	    		<tr><td valign="top"><p>&nbsp;<br/></p><p align="center" style="font-size:200%;color:<?= $nrColor ?>"><b><?= $req_param['assign'] ?></b></p></td></tr>
	    		<tr><td valign="bottom"><p align="center" style="font-size:100%;"><?= $nrCompetency ?><br/>&nbsp;</p></td></tr></table></td></tr>
	      <tr><td valign="bottom"><p align="center" style="font-size:100%;"><?= $lCompetency ?><br/>&nbsp;</p></td></tr>
	    </td></tr></table>
		<td width="4%">&nbsp;</td>
		<td width="84%" valign="top"><table class="invisible">
			<tr><td width="6%"><p>&nbsp;</p></td>
				<td width="88%"><p  style="font-size:200%">&nbsp;<br/></p><p id="heading-text" style="font-size:200%"><?= $heading ?></p></td>
  				<td width="6%"><small><select name="lang" id="lang_1" class="ddLangSelect"></select></small></td></tr></table></td>
  	  </tr><tr>
  		<td valign="top"><table class="invisible"><tr>
  	      <td width="20px" valign="top">&nbsp;</td>
  	      <td width="120px" valign="top">
<?php
	//check for videos
	if (strlen($req_param['tutor']) > 0) {
		$www = explode(",",$req_param['tutor']);
		foreach($www as $link) {
			echo '<img class="center fit tutorButton" src="img2/video.png" ><br/>';
		}
	}
    //check for weblinks
	if (strlen($req_param['www']) > 0) {
		$www = explode(",",$req_param['www']);
		foreach($www as $link) {
			echo '<a href="http://qur.at/'.$link.'" target="_TOP"><img class="center fit"  src="img2/link.png" ></a><br/>';
		}
	}
	// check for khanacademy
	if ($quicklink == "GM1B2") {
		echo '<table class="invisible"><tr><td width="80%">&nbsp;</td><td width="20%"><img class="center fit langFlag" src="flags/4x3/at.svg" style="height:10px;width:20px;"/></td></tr><td colspan="2">';	
		echo '<a id="khanLink" href="https://de.khanacademy.org/math/arithmetic/addition-subtraction/addition_carrying/v/carrying-when-adding-three-digit-numbers" target="_TOP"><img class="center fit"  src="img2/khanacademy.png" /></a>';
		echo '</td></tr></table>';
	}
	// check for sofatutor
	if ($quicklink == "GD1S128B1")
		echo '<a href="http://www.sofatutor.at/deutsch/videos/wie-sehen-verben-in-der-grundform-und-personalform-aus" target="_TOP"><img class="center fit" src="img2/sofatutor.jpg" /></a><br/>';
	// check for audio
	if ($quicklink == "GD1S93B2") {
		echo '<table class="invisible"><tr><td width="80%">&nbsp;</td><td width="20%"><img class="center fit langFlag" src="flags/4x3/at.svg" style="height:10px;width:20px;"/></td></tr><td colspan="2">';	
		echo '<a id="jquery_jplayer_1" href="javascript:sayText(\'Im Erzbachtal, dort, wo das Wasser des Leopoldsteiner Sees herunterrauscht, liegt eine Grotte. Wenn man hineingeht, findet man ein finsteres Wasserloch, in dem vor undenklichen Zeiten ein Wassermann gewohnt hat. An sonnigen Tagen kam der Wassermann aus dem dunklen Wasserloch geglitscht und legte sich vor dem Grotteneingang in die Sonne, um sich zu wärmen.\',2,3,2,\'S-2\');"><img class="center fit" src="img2/audio.jpg" /></a>';
		echo '</td></tr></table>';
	}
	// check for hints
	if (strlen($req_param['hint']) > 0)
		echo '<img id="opener" src="img2/info.png" >';
?>  
 		</td></tr></table></td><td width="4%">&nbsp;</td><td width="84%" valign="top"><table class="invisible">
<?php
	//place all the fields
	//$fCount global
	for ($i = 1; $i <= $fCount; $i++) {
		echo '<tr><td width="6%"><p></p></td><td width="88%"><p style="font-family: sans-serif;">';
		if ($insertSubNr) {
			if (((int) ($i / $subInterval)) == ($i / $subInterval)) {
				$label = $subLabels[(int) (($i / $subInterval) - 1)];
				if ( strlen($label) > 0  )
					echo $label . ")&nbsp;</p>";
			}
		}
		echo '<input type="text" name="entry_'.$i.'" id="inputTags_'.$i.'" style="width:100%" value="" /></td><td width="6%">&nbsp;<i id="status_'.$i.'" class="fa fa-circle-o icon-white fa-3x"></i>&nbsp;</td></tr>';
	}
?>
 		
  		</table></td></tr>
  	</tr><tr>
  		<td width="12%" ><table class="invisible"><tr>
  	      <td width="20px" valign="top">&nbsp;</td>
  	      <td width="120px"valign="top" align="left" background="img2/foto.png" style="background-repeat:no-repeat;height:100%;width:auto;background-position:center top;">
  	      	<i class="fa fa-paperclip fa-cog fa-3x"></i><br/>&nbsp;<br/>&nbsp;<br/>&nbsp;<br/>&nbsp;<br/></td></tr></table></td>
  	      <td width="4%">&nbsp;</td>
		  <td width="84%" valign="top"><table class="invisible">
			<tr><td width="6%"><p>&nbsp;</p></td>
				<td width="88%"><input id="filePic" type="file" name="image" accept="image/*" capture></td>
  				<td width="6%"><p>&nbsp;</p></td></tr></table></td>
 	</tr><tr>
		<td colspan="3">
 		 <center><img id="fotoImg" style="visibility: hidden;"></center>
		</td>
  	</tr><tr style="background-color: #CCCCCC;">
  		<td width="12%" ><p align="right" style="font-size:200%">Abgabe: <i class="fa fa-paper-plane-o"></i></p></td>
  		<td width="4%">&nbsp;</td>
		<td width="84%" valign="top"><table class="invisible">
  	  		<tr><td width="6%"></td>
  	  			<td width="120px" valign="bottom" align="right" >
  	      			<br/><img class="fit" src="img2/smile-ok.png" /></td>
  	  			<td width="120px" valign="bottom" align="right" >
  	      			<br/><img class="fit" src="img2/smile-hm.png" /></td>
  	      		<td width="120px" valign="bottom" align="right" >
  	      			<br/><img class="fit" src="img2/smile-nok.png" /></td>
  	      		<td><p align="right" style="font-size:200%">Chat:</p></td>
  	  			<td width="120px" id="chat-log" height="120px"><p><img width="120px" height="120px" src="img2/chat.png" ></p></td>
  	  			</tr>
		</table></td>
	</tr> 	
	</table>
 </td></tr></table>

<div id="dialog-info" title="Info">
  <p id="hint-text"><?= $hint ?></p>
</div>

<div id="dialog-chat-log" title="Chat und Activity Log"  class="dialog">
  <table class="invisible">
    <tr><td width="100%" bgcolor="white" colspan="2"><p align="left"><i class="fa fa-caret-down icon-cog fa-L"></i> morgen</p></td></tr>
	<tr><td width="10%"><a href="#"><img class="center fit" src="img2/bsp77.png" ></a></td><td width="90%"><p align="left">Mathe-H&Uuml;: Video ansehen <a href="https://www.youtube.com/watch?v=G8YSITorz8E&index=1&list=PL03E05147AF31CBD7"><i class="fa fa-youtube-play icon-nok fa-2x"></i></a></p><br/><a href="#"><i class="fa fa-thumbs-o-up fa-cog"> Like</i></a> <a href="#"><i class="fa fa-comment-o fa-cog"> Kommentar</i></a> <a href="#"><i class="fa fa-share-square-o fa-cog"> Weiterleiten</i></a></td></tr>
	<tr><td width="10%"><a href="#"><img class="center fit" src="img2/book.png" ></a></td><td width="90%"><p align="left">Deutsch-Test</p><br/><a href="#"><i class="fa fa-thumbs-o-up fa-cog"> Like</i></a> <a href="#"><i class="fa fa-comment-o fa-cog"> Kommentar</i></a> <a href="#"><i class="fa fa-share-square-o fa-cog"> Weiterleiten</i></a></td></tr>
    <tr><td width="100%" bgcolor="white" colspan="2"><p align="left"><i class="fa fa-caret-down icon-cog fa-L"></i> jetzt</p></td></tr>
	<tr><td width="10%"><a href="#"><img class="center fit" src="img2/bsp78.png" ></a></td><td width="90%"><p align="left">Mathe-H&Uuml; <a href="#"><i class="fa fa-pencil-square-o fa-cog fa-2x"></i></a> download <a href="#"><i class="fa fa-file-pdf-o icon-nok fa-2x"></i></a></p><br/><a href="#"><i class="fa fa-thumbs-o-up fa-cog"> Like</i></a> <a href="#"><i class="fa fa-comment-o fa-cog"> Kommentar</i></a> <a href="#"><i class="fa fa-share-square-o fa-cog"> Weiterleiten</i></a></td></tr>
	<tr><td width="10%">&nbsp;</td><td width="90%"><b>Max:</b> Danke f&uuml;r Deine Hilfe bei dem Beispiel! Als Dank bekommst Du meinen heutigen Orden! <i class="fa fa-certificate icon-gold fa-L fa-spin"></i><br/><a href="#"><i class="fa fa-thumbs-o-up fa-cog"> Like</i></a> <a href="#"><i class="fa fa-comment-o fa-cog"> Kommentar</i></a></td></tr>
	<tr><td width="10%">&nbsp;</td><td width="90%"><b>Lehrer:</b> Wenn Du Fragen hast - Video anschauen <a href="https://www.youtube.com/watch?v=G8YSITorz8E&index=1&list=PL03E05147AF31CBD7"><i class="fa fa-youtube-play icon-nok fa-L"></i></a> oder Freunde fragen!<br/><a href="#"><i class="fa fa-thumbs-o-up fa-cog"> Like</i></a> <a href="#"><i class="fa fa-comment-o fa-cog"> Kommentar</i></a></td></tr>
    <tr><td width="100%" bgcolor="white" colspan="2"><p align="left"><i class="fa fa-caret-right icon-cog fa-L"></i> heute</p></td></tr>
    
  </table>
</div>

<div id="dialog-tutor" title="Online Tutor" class="dialog">
  <p><img src="img2/video.png" > <?= $heading ?><br /> <iframe id="iframe-tutor" width="560" height="315" src="https://www.youtube.com/embed/<?= $link ?>" frameborder="0" allowfullscreen></iframe></p>
</div>

<div id="dialog-name" title="Willkommen!" class="dialog">
  <p><b>Wie magst Du genannt werden?</b><br /> <input type="text" name="entry_NN" id="inputTags_NN" style="width:100%" value="" onfocus="$(this).tagit('removeAll');" /></p>
</div>



<?php // NO BOOK SELECTED
} else {
	echo "</td></tr></table>";
}
?>
<script type="text/javascript">
try {

  //language
  var selLang = "<?php $lang ?>";

  //text to speech demo
  var demoText = new Array("Im Erzbachtal, dort, wo das Wasser des Leopoldsteiner Sees herunterrauscht, liegt eine Grotte. Wenn man hineingeht, findet man ein finsteres Wasserloch, in dem vor undenklichen Zeiten ein Wassermann gewohnt hat. An sonnigen Tagen kam der Wassermann aus dem dunklen Wasserloch geglitscht und legte sich vor dem Grotteneingang in die Sonne, um sich zu wärmen.",
		"Göl Leopold Steiner suları aşağı acele Erzbachtal, olarak, bir mağara vardır. Biri giderse, tek olmayan hayal kez bir Kova yaşadığı önce karanlık bir Paradise bulur. Güneşli günlerde, su adam koyu su delik geglitscht çıktı ve kendilerini ısıtmak için güneş mağara girişinde önünde uzandım.",
		"في Erzbachtal، حيث مياه بحيرة ليوبولد شتاينر يندفع إلى أسفل، هناك كهف. إذا كان أحد يذهب، يجد نفسه أمام اتيرهولي الظلام حيث قبل الأوقات يمكن تخيلها غير الممثلة عاش برج الدلو. في الأيام المشمسة، وجاء رجل المياه من حفرة للمياه الظلام geglitscht ووضع أمام مدخل الكهف في الشمس لwaÌrmen نفسها.",
		"在Erzbachtal，在湖利奥波德·施泰纳的海域冲了下来，有一个山洞。如果一个人去，你会发现一个黑暗的水潭，其中前非想象的时候住的水瓶座。在阳光明媚的日子里，水人走出黑暗的水坑geglitscht的躺了下来，在洞口前在阳光下取暖本身。",
		"В Erzbachtal, где воды озера Леопольда Штайнера бросается вниз, есть пещера. Если один идет, каждый находит темный водопоя, где раньше ООН-мыслимые раз жил Водолей. В солнечные дни, вода человек вышел из темной воды geglitscht отверстие и лег перед входом в пещеру на солнце, чтобы согре́ть себя.",
		"Dans Erzbachtal, où les eaux du lac Léopold Steiner se précipite en bas, il ya une grotte. Si l'on va, on trouve un point d'eau sombre où avant les temps impensable vivaient un Verseau. Les jours ensoleillés, l'homme de l'eau est sorti de l'obscurité trou d'eau glisser et se coucha en face de l'entrée de la grotte au soleil pour se réchauffer.",
		"En Erzbachtal, donde las aguas del Lago Leopold Steiner se precipita hacia abajo, hay una cueva. Si uno va, se encuentra un pozo de agua oscura donde antes de los tiempos imaginables impensable un Acuario. En los días soleados, el hombre del agua salió de la oscuridad desligarse pozo de agua y se acostó en frente de la entrada de la cueva en el sol para calentar sí.",
		"In Erzbachtal, where the waters of Lake Leopold Steiner rushes down, there is a cave. If one goes, one finds a dark waterhole where before unimaginable times lived an Aquarius. On sunny days, the water man came out of the dark water hole slipped and lay down in front of the cave entrance in the sun to warmen itself.",
		"In Erzbachtal, dove le acque del Lago di Leopold Steiner precipita giù, c'è una grotta. Se uno va, si trova una pozza d'acqua scura dove prima volte impensabile hanno vissuto un acquario. Nei giorni di sole, l'uomo acqua usciva dal buco acqua scura geglitscht e si sdraiò davanti all'ingresso della grotta sotto il sole per riscaldare sé.");
  var demoIndex = new Array("3", "16", "27", "10", "21", "4", "2", "1", "7");
  var demoLang = new Array("de", "tr", "ar", "zh-CN", "ru", "fr", "es", "en", "it");		

  //some security checks
  //====================
  //var home = "http://xyz.com/"; // app version
  var home = ""; // online version
  // encrypt
  var ENCRYPT_DATA = false;

  //have a look at the url
	function getUrlParameter(sParam)
	{
	    var sPageURL = window.location.search.substring(1);
	    var sURLVariables = sPageURL.split('&');
	    for (var i = 0; i < sURLVariables.length; i++) 
	    {
	        var sParameterName = sURLVariables[i].split('=');
	        if (sParameterName[0] == sParam) 
	        {
	            return sParameterName[1];
	        }
	    }
	}
  //check if we have a credential  
  function setCredential(cParam, cValue) {
    window.localStorage.setItem( "lip-acc-" + cParam, cValue );
    $("input[name='"+cParam+"']").val(cValue);
	return true;
  }	
  //check if we have a credential  
  function getCredential(cParam) {
	  //var credential = $("input[name='"+cParam+"']").first().val();
	  var credential = getUrlParameter(cParam);
	  if (!(undefined != credential))
		  credential = "";
	  if (credential.length > 1) {
		//if we have a credential set a cookie to remember ...
		window.localStorage.setItem( "lip-acc-" + cParam, credential );
	  } else {
		//if we have no token look if we have a cookie with the credential ...
		credential = window.localStorage.getItem("lip-acc-" + cParam);
		if ((typeof credential == null) || (credential == null))
			credential = "";
		else
			$("input[name='"+cParam+"']").val(credential);
	  }
	  return credential;
  }	
  
  //check if we have a token  
  var token = getCredential("t" );
  //check if we have a nickname  
  var nickname = getCredential("nick" );
  //XXX
  setCredential("s", "SALTED");
  
  //log the attempt
  function logActivity(aTag, aAction, aFId, aAnswer, aStatus) {								
	$.ajax({  						// send data off for logging
    	url: 'loggingRelay.php',  
		dataType: 'json',
		data: { t: token,				// who is it
			name: nickname,
			tag: aTag,  				// where we are
			mat: '<?= $quicklink ?>',
			action: aAction,			// what to log
			key: 'jgh98fdt4$dOsW',		// api key
			ts: '<?= $sTS ?>',			// session
			fieldId:  aFId,	// field
			ok<?= $logId ?>: aStatus, 	// checked
	    	hash: hash(aAnswer),		
	    	txt<?= $logId ?>: aAnswer	// plain
	    }, 
		success: function(result) {	// get feedback
			;//alert("ok");
		},  						// oops
		error: function(XMLHttpRequest, errorMsg, errorThrown) {
			;//alert(errorMsg);
		}	
	});
  }

  //foto capture
  oFReader = new FileReader();
	
  oFReader.onload = function (oFREvent) {		
	    document.getElementById("fotoImg").src = oFREvent.target.result;
	    document.getElementById("fotoImg").style.visibility = "visible"; 
	    var screenHeight = screen.availHeight;
	    screenHeight = screenHeight - 220;
	    document.getElementById("fotoImg").style.height = screenHeight;
  };

//DOCUMENT READY ---------
$( document ).ready(function() {
	
  //foto capture
  $("#filePic").button({
      icons: {
          primary: "fa fa-picture-o"
        },
        text: false
      });
  
  $("input:file").change(function (){
        var input = document.querySelector('input[type=file]');
        var oFile = input.files[0];
        oFReader.readAsDataURL(oFile);	
    });

  //nickname
  $( "#dialog-name" ).dialog({
    autoOpen: false,
    modal: true,
    buttons: {
    	Ok: function() {
        	  //let's check in the name
              
              //get the name
              nickname = $("#inputTags_NN").val();
              if (nickname.length < 1) 
                  nickname = "Nick.<?= rand(1,999) ?>";
              setCredential("nick", nickname);
              $.ajax({
          	     type:"GET",
          	     cache:false,
          	     url: home + 'actorRelay.php',
          	     dataType: 'json',
          	        data : {actorName : nickname, key : 'dOkPfdt4$dOsW' },
          	     success: function (result) {
              	     token = result.token;
              	     setCredential("t", token);
          	     }
          	   });	          
          	  $( this ).dialog( "close" );
            }
        }
  });

  //info
  $( "#dialog-info" ).dialog({
    autoOpen: false
  });

  $( "#opener" ).click(function() {
	logActivity( '<?= $tag ?>', 'attempt', <?= $logId ?>, 'hint', null);  
    $( "#dialog-info" ).dialog( "open" );
  });

  //chat log √∂ffnen
  $( "#dialog-chat-log" ).dialog({
      autoOpen: false,
      modal: false,
      width: "82%",
      buttons: [
            {
                text: "fertig",
                click: function() {
                   $( this ).dialog( "close" );
                }
            }
        ]
  });

  $( "#chat-log" ).click(function() {
      $( "#dialog-chat-log" ).dialog( "open" );
    });

  $( "#dialog-tutor" ).dialog({
	 	width: 600, // $(window).width(),
	    autoOpen: false
  });

  $( ".tutorButton" ).click(function() {
	 logActivity( '<?= $tag ?>', 'attempt', <?= $logId ?>, 'video', null);  
      $( "#dialog-tutor" ).dialog( "open" );
  });

  
  
  //quicklink tag
  var quicklinks = ['GM1B1','GM1B2','GM1B3','GM1B4','GM1B5','GM1B6','GM1B7','GM1B8','GM1B9','GM1B10','GM1B11','GM1B12','GM1B13','GM1B14','GM1B72','GM1B73','GM1B74','GM1B75','GD1S128B1','GD1S128B2','GD1S129B1','GD1S129B2','GD1S92B1','GD1S93B2','GD1S93B3'];
  $("#inputTags_QL").tagit({
	  allowSpaces: false, 
	  availableTags: quicklinks, 
	  maxTags: 1,
	  showAutocompleteOnFocus: true,
      beforeTagAdded: function(event, ui) {
          if(quicklinks.indexOf(ui.tagLabel) == -1)
          {
              return false;
          }
          if(ui.tagLabel == "not found")
          {
              return false;
          }
      },
      afterTagAdded: function(event, ui) {
    	  	window.location.replace("http://qur.at/QLk0U8Zb." + ui.tagLabel);
		  }
  });

  $("#inputTags_NN").tagit({
	  allowSpaces: false, 
	  maxTags: 1,
      afterTagAdded: function(event, ui) {
    	  	;
		  }
  });
  //this onfocus=".tagit('removeAll');"
  
  //input tags
<?php
	//check on results
	//fCount global
    $aEnc = explode(",", $req_param['aEnc']);
    $fAnswerCount = array();
    $fAnswerArray = array();
    for ($i = 1; $i <= $fCount; $i++) {	// init
    	$fAnswerCount[$i] = 0;
    	$fAnswerArray[$i] = array();
    }
    if ($req_param['aTyp'] != "NO")
	    foreach ($aEnc as $item) {			// look into each tupple
    		$itempair = explode(":",$item);
    		$i = intval($itempair[0]);
    		$fAnswerCount[$i]++;			// add to the list
   		 	array_push($fAnswerArray[$i], $itempair[1]);
    	}
	//place all the fields
	//$fCount global
	for ($i = 1; $i <= $fCount; $i++) {
		$logId = $ffId + $i;
		$checkAnswer = "false";
		if (($req_param['aTyp'] != "NO") && isset($fAnswerCount[$i]) && ($fAnswerCount[$i] > 0))
			$checkAnswer = "true";
		$partialAnswer = "false";
		if ($req_param['aTyp'] == "KEY"); // XXX ??? 
			$partialAnswer = "true";
		$intAnswer = "false";
		if ($req_param['aTyp'] == "INT")
			$intAnswer = "true";
		
?>
		var inputTag = $("#inputTags_<?= $i ?>");
			inputTag.tagit({allowSpaces: true, maxTags: 1,
			afterTagAdded: function(evt, ui) { 
				//check if we have an entry to the result field
				var resStatus = null;
				if (<?= $partialAnswer ?>) 
					var answer = inputTag.tagit("tagLabel", ui.tag).toLowerCase().split(" ");
				else 
					var answer = new Array(inputTag.tagit("tagLabel", ui.tag).trim());
				if (<?= $intAnswer ?>) 
					var answer = new Array(parseInt(inputTag.tagit("tagLabel", ui.tag).trim(), 10)); 
					
				$("#status_<?= $i ?>").removeClass("fa-circle-o").removeClass("icon-white").addClass("fa-check-circle-o").addClass("fa-cog");
				if (!ui.duringInitialization) {
					//do we know about correct answers?
					if (<?= $checkAnswer ?>) {
						validRes = [<?= implode(",",$fAnswerArray[$i]) ?>];
						//do we have a correct answer?
						var aIdx = 0;
						resStatus = false;
						for (idx = 0; idx < answer.length; ++idx)
							if (validRes.indexOf(hash(answer[idx])) >= 0) {
								resStatus = true;
								aIdx = idx;
							}	 
						//shall we show that we have a correct answer?
						if (!resStatus) { 
							$("#status_<?= $i ?>").removeClass("fa-check-circle-o").removeClass("fa-cog").removeClass("icon-white").addClass("fa-circle-o icon-nok"); 
						} else { 
							$("#status_<?= $i ?>").removeClass("fa-cog").removeClass("icon-white").addClass("icon-ok"); 
						}
					}
					//log the attempt
					logActivity( '<?= $tag ?>', 'attempt', <?= $logId ?>, answer[aIdx], resStatus);
				}		
		    },		
			afterTagRemoved: function(evt, ui) { 
				$("#status_<?= $i ?>").removeClass("fa-check-circle-o").removeClass("icon-nok").removeClass("icon-ok").addClass("fa-circle-o icon-white");
			}
		});
<?php		
	}
?>


	//translate - language choiches
	var ddLang = [
    {
        text: "",
        value: "de",
        selected: true,
        imageSrc: "flags/4x3/at.svg"
    },{
        text: "",
        value: "tr",
        selected: false,
        imageSrc: "flags/4x3/tr.svg"
    },{
        text: "",
        value: "ar",
        selected: false,
        imageSrc: "flags/4x3/sy.svg"
    },{
        text: "",
        value: "it",
        selected: false,
        imageSrc: "flags/4x3/it.svg"
    },{
        text: "",
        value: "en",
        selected: false,
        imageSrc: "flags/4x3/gb.svg"
    },{
        text: "",
        value: "es",
        selected: false,
        imageSrc: "flags/4x3/es.svg"
    },{
        text: "",
        value: "fr",
        selected: false,
        imageSrc: "flags/4x3/fr.svg"
    },{
        text: "",
        value: "ru",
        selected: false,
        imageSrc: "flags/4x3/ru.svg"
    },{
        text: "",
        value: "zh-CN",
        selected: false,
        imageSrc: "flags/4x3/cn.svg"
    }];

	//translate button
	$( "#lang_1" ).ddslick({
	    data: ddLang,
	    width: 116,
	    height: 168,
	    selectedIndex: 0,
	    onSelected: function (data) {			// check what is selected
	    	var idx = -1;
		    idx = data.selectedIndex;
		    if(idx > 0) {
				var selLang = ddLang[idx].value;
				// check if we have another flag anywhere
				if ($("#khanLink").length) {
					$(".langFlag").attr("src", ddLang[idx].imageSrc);
					$("#khanLink").attr("href", "https://" + selLang + $("#khanLink").attr("href").substr(10));
				}
				if ($("#jquery_jplayer_1").length) {
					$(".langFlag").attr("src", ddLang[idx].imageSrc);
					var dIdx = demoLang.indexOf(selLang);
					var dHref = "javascript:sayText('" + demoText[dIdx] + "',2," + demoIndex[dIdx] + ",2,'S-2');";
					$("#jquery_jplayer_1").attr("href", dHref);
				}
				var selText = '<?= $heading ?>---<?= $hint ?>';
		    	$.ajax({  						// send data off for translation
				    url: 'translationRelay.php',  
		    		dataType: 'json',
		    		data: { q: selText,  		// text to translate
			    		key: 'jgh98fdt4$poiu',
			    		format: 'html',
		            	source: 'de',
		            	target: selLang },   	// '|es' for auto-detect
		    		success: function(result) {	// get translated text
			    		var text = result.data.translations[0].translatedText.split('---');
			    		$('#heading-text').html(text[0]);
						$('#hint-text').html(text[1]);
		    		},  						// oops
		    		error: function(XMLHttpRequest, errorMsg, errorThrown) {
		        		alert(errorMsg);
		    		}	
		    	}); 				 
			} else {							// back to default with language 0
				$('#heading-text').text('<?= $heading ?>');
				$('#hint-text').html('<?= $hint ?>');
			}			    
	    }    
	});

	//check if we have a name
    if (nickname.length < 1)
	  $( "#dialog-name" ).dialog( "open" );

  });
} catch (err) {
	alert(err);
}
</script>
</body></html>