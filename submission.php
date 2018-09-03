<?php

// 1st post-get trigger
$filename=$_POST['filename1'];
if (isset($_POST['filename1'])){
exec("sudo openocd -f /usr/local/share/openocd/scripts/interface/raspberrypi4-native.cfg -f /usr/local/share/openocd/scripts/cpld/xilinx-xc6s.cfg -c \"init; xc6s_program xc6s.tap; pld load 0 /home/pi/myshare/$filename.bit; exit\"");
echo $filename.".bit file successfully uploaded to FPGA";

}

// 2nd post-get trigger
$timevec = $_POST['t_vec1'];
$statevec = $_POST['s_vec1'];
$loopvec = $_POST['l_vec1'];
if(isset($_POST['t_vec1'])){
#echo $timevec;
#echo "<br>";
#echo $statevec;

$file = file('setting.txt');
$data = $timevec;
$some_index = 0; // line 0 set for time data
foreach($file as $index => $line){
   if($index == $some_index){ $file[$index] = $data . "\n"; }
}
$content = implode($file);

$data = $statevec;
$some_index = 1; // line 1 set for state data
foreach($file as $index => $line){
   if($index == $some_index){ $file[$index] = $data . "\n"; }
}
$content = implode($file);

$data = $loopvec;
$some_index = 2; // line 3 set for loop data
foreach($file as $index => $line){
   if($index == $some_index){ $file[$index] = $data . "\n"; }
}
$content = implode($file);

file_put_contents('setting.txt', $content);
$output = shell_exec("sudo python spi_test.py");
echo $output;
}

// 3rd post-get trigger
$rc = $_POST['set_rc'];
if(isset($_POST['set_rc'])){
echo $rc;

$file = file('setting.txt');
$data = $rc;
$some_index = 3;  // line 3 set for row coloumn size data
foreach($file as $index => $line){
   if($index == $some_index){ $file[$index] = $data . "\n";   }
}
$content = implode($file);
file_put_contents('setting.txt', $content);
}


// 4th post-get trigger
$trg = $_POST['trig'];
if(isset($_POST['trig'])){
$output = shell_exec("sudo python trig.py");
echo $output;
echo "<br>";
echo "done";
}


?>
