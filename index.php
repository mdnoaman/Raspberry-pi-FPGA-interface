<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<head>
<title>Submit Form Using AJAX and jQuery</title>
<script src="jquery-3.3.1.min.js"></script>

<link rel="stylesheet" href="style_file.css" type="text/css", media="screen, projection"/>
</head>
<body style="background-color:#48C9B0;">

<h2>FPGA Sequence Generator</h2>
<script>
$(document).ready(function(){
	
$("#load").click(function(){
var filename = $("#filename").val();
var dataString = 'filename1='+ filename;
if(filename=='')
{
$("#display").html("Please enter file name");
$("#display2").html("");
$("#display3").html("");
}
else
{
$.ajax({
type: "POST",
url: "submission.php",
data: dataString,
cache: false,
success: function(result){
$("#display").html(result);
$("#display2").html("");
$("#display3").html("");
}
});
}
return false;
});


$("#send_seq").click(function(){
var ro = $('#ro').val();
var co = $('#co').val();
var n_rows = parseInt(ro, 10);
var n_cols = parseInt(co, 10); 

var t_arr = "";
var warn = 0;
for (i=1; i<n_cols +1; i++){
	t_arr += $('#t_vec'+i).val() + ",";
	if($('#t_vec'+i).val() !=""){
		warn += 0 ;
	}
	else{
		warn += 1;
	}
}

var s_arr = "";
for (j=1;j<n_cols+1;j++){
	for (i=0;i<n_rows;i++){
		
		if ($('#c'+j+'r'+i).is(":checked")){
			s_arr +='1';}
		else{
			s_arr +='0';
			}
	}
	s_arr += ','; 
}

l_arr = $('#t1s').val()+','+$('#t1e').val()+','+$('#n1').val() +','+$('#t2s').val() +','+$('#t2e').val()+','+$('#n2').val() +',';

var dataString = 't_vec1='+ t_arr +'&s_vec1=' +s_arr +'&l_vec1=' + l_arr;
if(warn != 0)
{
$("#display").html("Parameters not loaded! Please set all the time inputs.");
$("#display2").html(t_arr);
$("#display3").html(s_arr);
}
else
{
$.ajax({
type: "POST",
url: "submission.php",
data: dataString,
cache: false,
success: function(result){
$("#display").html("Parameters successfully loaded!");
$("#display2").html(result);
$("#display3").html("");
//location.reload();
}
});
}
return false;
});


$("#set_rc").click(function(){
var ro = $('#ro').val();
var co = $('#co').val();
var hd = 'set_rc=';
var dataString = hd + ro +','+co;

$.ajax({
type: "POST",
url: "submission.php",
data: dataString,
cache: false,
success: function(result){
$("#display").html(dataString);
$("#display2").html(result);
$("#send_seq").click();
location.reload();
}
});
});



$("#trig").click(function(){
var hd = 'trig=';
var vv = '1';
var dataString = hd + vv;

$.ajax({
type: "POST",
url: "submission.php",
data: dataString,
cache: false,
success: function(result){
$("#display").html(dataString);
$("#display2").html(result);
$("#display3").html("");
}
});
});

});

setInterval("my_function();",100); 
function my_function(){
  $('#time').load(location.href + ' #time');
}

</script>


<div id="time">
<?php echo date('H:i:s');
echo "<br>";
?>
</div> 

<?php
	$myFile = "setting.txt";
	$lines = file($myFile);//file in to an array
	$frd0 = $lines[0]; //line 0 for time data
	$frd1 = $lines[1]; //line 1 for state data
	$frd2 = $lines[2]; //line 2 for row col config data
	$frd3 = $lines[3]; //line 2 for row col config data
	fclose($myfile);

	$time_arr=explode(",",$frd0);
	$state_arr=explode(",",$frd1);
	$nloop = explode(",",$frd2);
	$rowcol=explode(",",$frd3);
//	echo $crr[1];
//	echo $time_arr[10];
?>

<?php
$rows = $rowcol[0];
$cols = $rowcol[1];
echo "rows: <input value = $rows id = \"ro\" type=\"number\" min=\"1\" step=\"1\" max=\"100\"style=\"width: 3em\">";
echo " columns: <input value = $cols id = \"co\" type=\"number\" min=\"1\" step=\"1\" max=\"100\"style=\"width: 3em\">";
echo "<input type=\"submit\" value=\"Reload page\" id=\"set_rc\">";

$t1ss = $nloop[0];
$t1ee = $nloop[1];
$n1se = $nloop[2];
$t2ss = $nloop[3];
$t2ee = $nloop[4];
$n2se = $nloop[5];

echo " T1 start: <input value=$t1ss id = \"t1s\" type=\"number\" min=\"1\" step=\"1\" max=\"10000\"style=\"width: 3em\">";
echo "end: <input value=$t1ee id = \"t1e\" type=\"number\" min=\"1\" step=\"1\" max=\"10000\"style=\"width: 3em\">";
echo "N1<input value=$n1se id = \"n1\" type=\"number\" min=\"1\" step=\"1\" max=\"10000\"style=\"width: 3em\">";
echo "   T2 start: <input value=$t2ss id = \"t2s\" type=\"number\" min=\"1\" step=\"1\" max=\"10000\"style=\"width: 3em\">";
echo "end: <input value=$t2ee id = \"t2e\" type=\"number\" min=\"1\" step=\"1\" max=\"10000\"style=\"width: 3em\">";
echo "N2<input value=$n2se id = \"n2\" type=\"number\" min=\"1\" step=\"1\" max=\"10000\"style=\"width: 3em\">";

echo "<div id=\"overflowTest\">";

echo "<table>";
	echo "<tr>";
    for ($i=0; $i < $cols+1; $i ++){
		if ($i ==0){
			echo"<th>Steps</th>";
		}
		else{
			echo "<th>Time $i</th>";
		}
	}
	echo  "</tr>";

	echo "<tr>";
    for ($i=0; $i < $cols+1; $i ++){
		$k = $i - 1;
		$tt = 't_vec'.$i;
		if ($i ==0){
			echo"<td>10ns</td>";
		}
		else{
			echo "<td><input value = $time_arr[$k] id = $tt type=\"number\" min=\"1\" step=\"1\" max=\"100000000\"style=\"width: 5em\"></td>";
		}
	}
	echo "</tr>";
	
	for ($j=0; $j < $rows; $j++){
		echo "<tr>";
		for ($i=0; $i < $cols+1; $i ++){
			$s = $i-1;
			if($state_arr[$s][$j] == 1){
				$state = "checked";
			}
			else{
				$state ="";
			}
			$ss = 'c'.$i.'r'.$j;
			if ($i ==0){
				echo"<td>Ch $j</td>";
			}
			else{
				echo "<td><label class=\"switch\"><input id =$ss type=\"checkbox\" $state><span class=\"slider\"></span></label></td>";
			}
		}
		echo "</tr>";
	}
	
echo "</table>";
echo "</div>";
?>

<!-- <input type="file" name="myFile" id="input_file">
<input type="submit" value="save to file">
<input type="submit" value="recall from file">
-->
<input type="submit" value="Load sequence" id="send_seq">
<input type="submit" value="Trigger" id="trig">
 Load FPGA configuration <input id="filename" type="text" placeholder="bit file" size="8">
<input id="load" type="button" value="Load">
<br>
<div id="display"> Status... </div>
<div class="test" id="display2" style="word-break:break-word;"></div>
<div class="test" id="display3" style="word-break:break-word;"></div>


</body>
</html>
