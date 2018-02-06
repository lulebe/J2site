<?php
$secure = true;
require '../init.inc.php';

$survey = $db->getSurvey($_GET['id'], $_SESSION['userid']);

$labels = array();
$data = array();
foreach ($survey['answers'] as $key=>$el) {
  array_push($labels, $el['content']);
  array_push($data, $el['count']);
}





/* pChart library inclusions */
include("../pchart/class/pData.class.php");
include("../pchart/class/pDraw.class.php");
include("../pchart/class/pPie.class.php");
include("../pchart/class/pImage.class.php");

/* Create and populate the pData object */
$MyData = new pData();   
$MyData->addPoints($data,"ScoreA");  
$MyData->setSerieDescription("ScoreA","Application A");

/* Define the absissa serie */
$MyData->addPoints($labels,"Labels");
$MyData->setAbscissa("Labels");
$MyData->loadPalette("../pchart/palettes/gray.color", TRUE);

/* Create the pChart object */
$myPicture = new pImage(800,650,$MyData);

/* Write the picture title */ 
$myPicture->setFontProperties(array("FontName"=>"../pchart/fonts/calibri.ttf","FontSize"=>30));
$myPicture->drawText(20,40,$survey['name'],array("R"=>0,"G"=>0,"B"=>0));

/* Set the default font properties */ 
$myPicture->setFontProperties(array("FontName"=>"../pchart/fonts/verdana.ttf","FontSize"=>17,"R"=>0,"G"=>0,"B"=>0));

/* Create the pPie object */ 
$PieChart = new pPie($myPicture,$MyData);

/* Draw an AA pie chart */ 
$PieChart->draw2DPie(485,350,array("Radius"=>250, "WriteValues"=>TRUE, "ValuePadding"=>30, "ValueR"=>0, "ValueG"=>0, "ValueB"=>0, "Border"=>TRUE, "BorderR"=>0, "BorderG"=>0, "BorderB"=>0));

/* draw legend */
$PieChart->drawPieLegend(0,100,array("R"=>255,"G"=>255,"B"=>255, "BoxSize"=>20));

/* Render the picture */
$myPicture->Stroke();

?>