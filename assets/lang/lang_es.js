
$_s = new Array();

$_s["sizes_guide_1"]="34";
$_s["sizes_guide_2"]="36";
$_s["sizes_guide_3"]="38";
$_s["sizes_guide_4"]="40";
$_s["sizes_guide_5"]="42";
$_s["sizes_guide_6"]="44";
$_s["sizes_guide_7"]="46";
$_s["sizes_guide_8"]="48";
$_s["sizes_guide_9"]="50";
$_s["sizes_guide_10"]="52";

$_styles = new Array();

$(document).ready(function() {
  $(".data-lang").each(function(){
    $(this).html($_s[$(this).attr("data-lang")]);
  });
});
