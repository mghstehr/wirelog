<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>
<?php
   $day = date("d");
   $month = date("m");
   $year = date("y");
   print("WireLog $day/$month/$year");
?>
    </title>

    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load('visualization', '1', {packages: ['corechart']});
    </script>
    <script type="text/javascript">
      function drawVisualization() {
        // Create and populate the data table.
              var data = new google.visualization.DataTable();
<?php
   include("logFileParser.inc");
   //$day = date("d");
   //$month = date("m");
   //$year = date("y");
   $sensors = array("none", "Collector Fluid", "Hot Water Output", "Solar Storage High", "External Heating System", "Outside temperature", "Solar Storage Low", "temp1", "temp2", "temp3", "temp4", "temp5", "temp6", "temp7", "temp8", "temp9", "temp10", "temp11");
   $colors = array("black", "grey", "red", "orange", "BlueViolet", "green", "blue");
   $lines = generateXYForOneDay($day, $month, $year);
   print("data.addColumn('datetime', 'time');\n");
   $nbOfLines=sizeof($lines);
   $nbOfMeasurements=sizeof($lines[0]);
   $generalMax=0;
   for($i=1; $i<$nbOfLines; $i++) {
      print("data.addColumn('number', '$sensors[$i]');\n");
      for($j=0; $j<$nbOfMeasurements; $j++) {
         $value = $lines[$i][$j];
         if ($value > $generalMax) {
            $generalMax = $value;
         }
      }
   }
   //print("General max is $generalMax");
   for($i=0; $i<$nbOfMeasurements; $i++) {
      $value = $lines[0][$i];
      print("data.addRow([new Date($value)");
      //print("$value");
      for($j=1; $j<sizeof($lines); $j++) {
         $value = $lines[$j][$i];
         print(", $value");
      }
      print("]);\n");
   }
?>
        // Create and draw the visualization.
        new google.visualization.LineChart(document.getElementById('visualization')).
            draw(data, {curveType: "function",
                        width: 1000, height: 800,
<?php
            print("vAxis: {maxValue: $generalMax}}");
?>
                );
      }
      

      google.setOnLoadCallback(drawVisualization);
    </script>
  </head>
  <body style="font-family: Arial;border: 0 none;">
<?php
   $day = date("d");
   $month = date("m");
   $year = date("y");
   print("<H1><center>WireLog $day/$month/$year</center></H1>");
?>
    <div id="visualization" style="width: 1000px; height: 800px;"></div>
  </body>
</html>
