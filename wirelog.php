<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<?php
   $day = date("d");
   $month = date("m");
   $year = date("y");
   $graphTitle="Wirelog ".$day."/".$month."/".$year;
?>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load('visualization', '1', {packages: ['corechart']});
    </script>
    <script type="text/javascript">
      var view;
      var options;
      var maximumValues=new Array();
      // Create the data table.
      var data = new google.visualization.DataTable();
      var query;
      var t;

      function drawVisualization() {
<?php
   include_once("logFileParser.inc");
   include_once("settings.inc");

   // Read data and in order to generate maximum values (TODO maybe to be changed...)
   $lines = generateXYForOneDay($day, $month, $year);

   $nbOfLines=sizeof($lines);
   $nbOfMeasurements=sizeof($lines[0]);
   for($i=1; $i<$nbOfLines; $i++) {
      $maxSensor[$i] = -272;
   }
   for($i=1; $i<$nbOfLines; $i++) {
      for($j=0; $j<$nbOfMeasurements; $j++) {
         $value = $lines[$i][$j];
         // Calculate max value per sensor
         if ($value > $maxSensor[$i]) {
            $maxSensor[$i] = $value;
         }
      }
   }
   $generalMax=-272;
   print("         maximumValues.push(0);\n"); // No sensor 0
   for($i=1; $i<$nbOfLines; $i++) {
      print("         maximumValues.push($maxSensor[$i]);\n");
      if ($maxSensor[$i] > $generalMax) {
         $generalMax = $maxSensor[$i];
      }
   }

   for($i=0; $i<$nbOfMeasurements; $i++) {
      $value = $lines[0][$i];
      for($j=1; $j<sizeof($lines); $j++) {
         $value = $lines[$j][$i];
      }
   }
?>
         // Create and draw the visualization.
         options = { curveType: "function", interpolateNulls: true, 
                     chartArea:{left:50,top:50, width:"90%", height:"75%"},
<?php
   print("                     title: '$graphTitle',\n");
   print("                     legend: 'none',\n");
   print("                     vAxis: {maxValue: $generalMax, title:'Temperatures', gridlines:{count:10}}\n");
   $first = true;
   print("                         , colors:[");
   for($i=1; $i<$nbOfLines; $i++) {
      if ($first) {
         print("'$colors[$i]'");
         $first=false;
      } else {
         print(",'$colors[$i]'");
      } 
   }
   print("]\n");

   print("          };\n");
?>
         // Make a Query to datasource
         // Create a view (to be able to hide / show measurement)
         query = new google.visualization.Query('http://patrice.den.free.fr/wirelog/datasource.php');
         query.setQuery('select:today');
         //query.setRefreshInterval(20); // not working
         // Send the query with a callback function.
         query.send(handleQueryResponse);
      }

      function resendQuery()
      {
         query.send(handleQueryResponse);
      }

      function handleQueryResponse(response) {
         if (response.hasWarning()) {
            // No data
            t=setTimeout("resendQuery()",60000);
            return;
         } else {
            data = response.getDataTable();
            view = new google.visualization.DataView(data);
            var linechart = new google.visualization.LineChart(document.getElementById('visualization'));
            linechart.draw(view, options);
            t=setTimeout("resendQuery()",60000);
         }
      }

      function clickOnSensor() {
<?php
      $listOfColumns="[0";
      for($i=1; $i<$nbOfLines; $i++) {
         $listOfColumns = $listOfColumns.",".$i;
      }
      $listOfColumns = $listOfColumns."]";
      print("         var updatedColors = new Array();\n");
      print("         var maxTemperature=-272;\n");
      print("         view.setColumns($listOfColumns);\n"); 
      for($i=1; $i<$nbOfLines; $i++) {
         print("         if (document.getElementById('buttonSensor$i').checked == 0) {\n"); 
         print("            view.hideColumns([$i]);\n");
         print("         } else {\n"); 
         print("            // Push back color in colorOption \n");
         print("            updatedColors.push('$colors[$i]');\n");
         print("            if (maximumValues[$i] > maxTemperature) {\n");
         print("               maxTemperature = maximumValues[$i];\n");  
         print("            }\n");
         print("         }\n"); 
      }
      print("         options.colors=updatedColors;\n"); 
      print("         options.vAxis.maxValue=maxTemperature;\n"); 
      print("         var linechart = new google.visualization.LineChart(document.getElementById('visualization')).draw(view, options);\n");
?>
      }

      google.setOnLoadCallback(drawVisualization);
    </script>
  </head>
  <body style="font-family: Arial;border: 0 none;">
   <div id="container" style="width:840px; height.480px; position:relative;">
      <div id="visualization" style="width: 640px; height:480px; float:left;"></div>
      <div id="menu" style="width:200px;height:480px;position:relative;float:right;">
<?php
   for($i=1; $i<$nbOfLines; $i++) {
      print("         <p style=\"color:$colors[$i]\"><input id=buttonSensor$i type='checkbox' checked='checked' onclick='clickOnSensor()' />$sensors[$i]</p>\n");
   }
?>
      </div>
   </div>

  </body>
</html>
