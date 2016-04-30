<?php
/**
 * @package PluginService
 */

/**
 * chartService - 
 * class chartService is the plug-in service of printing bizform to chart 
 * 
 * @package PluginService
 * @author rocky swen 
 * @copyright Copyright (c) 2003
 * @version $Id: chartService.php,v 1.1 2006/04/07 07:59:46 rockys Exp $
 * @access public 
 */
class chartService
{
   /**
    * List of plot objects used in drawing group plot and accurated plot
    * 
    * @var array 
    */
   var $m_PlotList;
   
   /**
    * chartService::chartService()
    * 
    * @param void
    * @return void 
    */
   function chartService() {}
   
   /**
    * chartService::render() - render the chart output
    * 
    * @param string $objname object name which is the bizform name
    * @param string $inputDataStr client input data with format key=value#-#-#k2=v2;...
    * @return void 
    */
   function render($objname)
   {
      global $g_BizSystem;
      // get the value of the control that issues the call 
      $chartName = $g_BizSystem->GetClientProxy()->GetFormInputs("__this");
      
      // get the current UI bizobj
      $bizform = $g_BizSystem->GetObjectFactory()->GetObject($objname);    // get the existing bizform object 
      $bizobj = $bizform->GetDataObj();
      
      // get chart config xml file
      $chart_xmlfile = BizSystem::GetXmlFileWithPath($objname."_chart");
      $xmlArr = BizSystem::GetXmlArray($chart_xmlfile);

      ob_clean();
      // get the chart section from config xml file
      foreach($xmlArr["BIZFORM_CHART"]["CHARTLIST"]["CHART"] as $chart) {
         if (count($xmlArr["BIZFORM_CHART"]["CHARTLIST"]["CHART"]) == 1)
            $chart = $xmlArr["BIZFORM_CHART"]["CHARTLIST"]["CHART"];
         // try to match the chartName, if no chartName given, always draw the first chart defined in xml file
         if (($chartName && $chart["ATTRIBUTES"]["NAME"] == $chartName) || !$chartName) {
            if ($chart["ATTRIBUTES"]["GRAPHTYPE"] == 'XY') {
               $this->XYGraphRender($bizobj, $chart);
               break;
            }
            if ($chart["ATTRIBUTES"]["GRAPHTYPE"] == 'Pie') {
               $this->PieGraphRender($bizobj, $chart);
               break;
            }
         }
      }
   }
   
   /**
    * chartService::GetPlotData() - get plot data array
    * 
    * @param BizObj $bizobj object reference of bizobj
    * @param array $fields list of bizobj fields
    * @param array $labelFld label field of bizobj
    * @return array reference of the array [field][index] 
    */
   function &GetPlotData(&$bizobj, $fields, $labelFld)
   {
      $oldCacheMode = $bizobj->GetCacheMode(); 
      $bizobj->SetCacheMode(0);    // turn off cache mode, not affect the current cache
      $bizobj->RunSearch(-1);  // don't use page search
      while (1)
      {
        $recArray = $bizobj->GetRecord(1);
        if (!$recArray) break;
        $bizobj->UnformatInputRecArr($recArray);
        
        foreach($fields as $fld)
            $recMatrix[$fld][] = $recArray[$fld];   // get data without format
        $recMatrix[$labelFld][] = $recArray[$labelFld];   // get symbol with format
      }
      $bizobj->SetCacheMode($oldCacheMode); 
      return $recMatrix;
   }

   /**
    * chartService::XYGraphRender() - draw the XY type graph (can have > 1 plots)
    * 
    * @param BizObj $bizobj object reference of bizobj
    * @param array $xmlArr xml array reference
    * @return void
    */
   function XYGraphRender(&$bizobj, &$xmlArr)
   {
      include_once (JPGRAPH_DIR.'/jpgraph.php'); 
      
      $graph = new Graph($xmlArr["ATTRIBUTES"]["WIDTH"],$xmlArr["ATTRIBUTES"]["HEIGHT"],"auto");
      //$graph->img->SetAntiAliasing(); 
      $graph->SetScale("textlin"); 
      $graph->yaxis->scale->SetGrace(10);
      list($m1, $m2, $m3, $m4) = split(',', $xmlArr["ATTRIBUTES"]["MARGIN"]);
      $graph->img->SetMargin($m1, $m2, $m3, $m4);
      
      // get the data set 
      foreach($xmlArr['DATASET']['DATA'] as $dtmp) {
         if ($xmlArr['DATASET']['DATA']['ATTRIBUTES'])
            $dtmp = $xmlArr['DATASET']['DATA'];
         $fieldName = $dtmp["ATTRIBUTES"]["FIELD"];
         if ($fieldName)
            $fields[$fieldName] = $fieldName;
      }
      
      $labelFld = $xmlArr['XAXIS']['ATTRIBUTES']['LABELFIELD'];
      
      $recArray = &$this->GetPlotData($bizobj, $fields, $labelFld);

      $i = 0;
      foreach($xmlArr['DATASET']['DATA'] as $dtmp) {
         if ($xmlArr['DATASET']['DATA']['ATTRIBUTES'])
            $dtmp = $xmlArr['DATASET']['DATA'];
         $data = $recArray[$dtmp["ATTRIBUTES"]["FIELD"]];
         $plot = $this->RenderXYPlot($data, $dtmp);
         if ($plot)
            $graph->Add($plot);
         $i++;
      }

      // render titles
      $graph->title->Set($xmlArr['TITLE']['ATTRIBUTES']['CAPTION']);
      $this->DrawString($graph->title,$xmlArr['TITLE']['ATTRIBUTES']['FONT'],$xmlArr['TITLE']['ATTRIBUTES']['COLOR']); 
      
      // render xaxis
      $this->DrawAxis($graph->xaxis, $recArray[$labelFld], 
                      $xmlArr['XAXIS']['ATTRIBUTES']['FONT'], $xmlArr['XAXIS']['ATTRIBUTES']['COLOR'],
                      $xmlArr['XAXIS']['ATTRIBUTES']['LABELANGLE'], $xmlArr['XAXIS']['ATTRIBUTES']['TITLE'],
                      $xmlArr['XAXIS']['ATTRIBUTES']['TITLEFONT'], $xmlArr['XAXIS']['ATTRIBUTES']['TITLECOLOR'],
                      $xmlArr['XAXIS']['ATTRIBUTES']['TITLEMARGIN']);
      // render yaxis
      $this->DrawAxis($graph->yaxis, null, 
                      $xmlArr['YAXIS']['ATTRIBUTES']['FONT'], $xmlArr['YAXIS']['ATTRIBUTES']['COLOR'],
                      $xmlArr['YAXIS']['ATTRIBUTES']['LABELANGLE'], $xmlArr['YAXIS']['ATTRIBUTES']['TITLE'],
                      $xmlArr['YAXIS']['ATTRIBUTES']['TITLEFONT'], $xmlArr['YAXIS']['ATTRIBUTES']['TITLECOLOR'],
                      $xmlArr['YAXIS']['ATTRIBUTES']['TITLEMARGIN']);

      // render legend
      $this->DrawLegend($graph->legend,$xmlArr['LAGEND']['ATTRIBUTES']['POSITION'],
                        $xmlArr['LAGEND']['ATTRIBUTES']['LAYOUT'], $xmlArr['legend']['ATTRIBUTES']['FONT'],
                        $xmlArr['LAGEND']['ATTRIBUTES']['COLOR'], $xmlArr['legend']['ATTRIBUTES']['FILLCOLOR']);
      $graph->Stroke();
   }
   
   /**
    * chartService::PieGraphRender() - draw the Pie type graph (can have 1 pie plot)
    * 
    * @param BizObj $bizobj object reference of bizobj
    * @param array $xmlArr xml array reference
    * @return void
    */
   function PieGraphRender(&$bizobj, &$xmlArr)
   {
      include_once (JPGRAPH_DIR.'/jpgraph.php'); 
      include_once (JPGRAPH_DIR.'/jpgraph_pie.php'); 
      include_once (JPGRAPH_DIR.'/jpgraph_pie3d.php');
      
      $graph = new PieGraph($xmlArr["ATTRIBUTES"]["WIDTH"],$xmlArr["ATTRIBUTES"]["HEIGHT"]);
      //$graph->SetAntiAliasing(); 

      // get the data set - only support one data
      $fields[0] = $xmlArr['DATASET']['DATAPIE']["ATTRIBUTES"]["FIELD"];
      $legendFld = $xmlArr['DATASET']['DATAPIE']["ATTRIBUTES"]["LEGENDFIELD"];

      $recArray = &$this->GetPlotData($bizobj, $fields, $legendFld);

      $chartData = $xmlArr['DATASET']['DATAPIE'];
      $plot = $this->RenderPiePlot($recArray[$fields[0]], $chartData);
      $plot->SetLegends($recArray[$legendFld]);
      $graph->Add($plot);

      // render titles
      $graph->title->Set($xmlArr['TITLE']['ATTRIBUTES']['CAPTION']); 
      $this->DrawString($graph->title,$xmlArr['TITLE']['ATTRIBUTES']['FONT'],$xmlArr['TITLE']['ATTRIBUTES']['COLOR']);
      
      // render legend
      $this->DrawLegend($graph->legend,$xmlArr['LEGEND']['ATTRIBUTES']['POSITION'],
                        $xmlArr['LEGEND']['ATTRIBUTES']['LAYOUT'], $xmlArr['LEGEND']['ATTRIBUTES']['FONT'],
                        $xmlArr['LEGEND']['ATTRIBUTES']['COLOR'], $xmlArr['LEGEND']['ATTRIBUTES']['FILLCOLOR']);
      $graph->Stroke();
   }
   
   /**
    * chartService::RenderXYPlot() - draw the XY type plot
    * 
    * @param array $data plot data array reference 
    * @param array $xmlArr xml array reference
    * @return object refernce XY plot object reference
    */
   function RenderXYPlot(&$data, &$xmlArr)
   {
      $id = $xmlArr['ATTRIBUTES']['ID'];
      $field = $xmlArr['ATTRIBUTES']['FIELD'];
      $chartType = $xmlArr['ATTRIBUTES']['CHARTTYPE'];
      $pointType = $xmlArr['ATTRIBUTES']['POINTTYPE'];
      $weight = $xmlArr['ATTRIBUTES']['WEIGHT'];
      $color = $xmlArr['ATTRIBUTES']['COLOR'];
      $fillColor = $xmlArr['ATTRIBUTES']['FILLCOLOR'];
      $showVal = $xmlArr['ATTRIBUTES']['SHOWVALUE'];
      $legend = $xmlArr['ATTRIBUTES']['LEGENDFIELD'];
      $visible = $xmlArr['ATTRIBUTES']['VISIBLE'];
      
      if ($chartType == 'Line' or $chartType == 'Bar') {
         if ($chartType == 'Line') {
            include_once (JPGRAPH_DIR.'/jpgraph_line.php'); 
            $plot = new LinePlot($data);
            $this->DrawMark($plot->mark, 
                            $xmlArr['POINTMARK']['ATTRIBUTES']['TYPE'], $xmlArr['POINTMARK']['ATTRIBUTES']['COLOR'],
                            $xmlArr['POINTMARK']['ATTRIBUTES']['FILLCOLOR'], $xmlArr['POINTMARK']['ATTRIBUTES']['SIZE']);
            $plot->SetBarCenter();
            $plot->SetCenter(); 
         }
         else if ($chartType == 'Bar') {
            include_once (JPGRAPH_DIR.'/jpgraph_bar.php'); 
            $plot = new BarPlot($data);
            $plot->SetAlign('center'); 
         }
         if ($color) $plot->SetColor($color);
         if ($fillColor) $plot->SetFillColor($fillColor);
         if ($weight) $plot->SetWeight($weight);
         if ($showVal == 1) $plot->value->Show();
         if ($legend) $plot->SetLegend($legend);
         $this->DrawString($plot->value,$xmlArr['VALUE']['ATTRIBUTES']['FONT'],$xmlArr['VALUE']['ATTRIBUTES']['COLOR']);
      }
      
      if ($chartType == 'GroupBar' or $chartType == 'AccBar') {
         $children = $xmlArr['ATTRIBUTES']['CHILDREN'];
         $childList = split(",",$children);
         foreach($childList as $child) {
            $childPlotList[] = $this->m_PlotList[$child];
         }
         if ($chartType == 'GroupBar')
            $plot = new GroupBarPlot($childPlotList);
         else if ($chartType == 'AccBar')
            $plot = new AccBarPlot($childPlotList);
      }
      
      $this->m_PlotList[$id] = $plot;

      if ($visible == 1)
         return $plot;
      return null;
   }
   
   /**
    * chartService::RenderPiePlot() - draw the Pie type plot
    * 
    * @param array $data plot data array reference 
    * @param array $xmlArr xml array reference
    * @return object refernce Pie plot object reference
    */
   function RenderPiePlot(&$data, &$xmlArr)
   {
      $id = $xmlArr['ATTRIBUTES']['ID'];
      $field = $xmlArr['ATTRIBUTES']['FIELD'];
      $chartType = $xmlArr['ATTRIBUTES']['CHARTTYPE'];
      $size = $xmlArr['ATTRIBUTES']['SIZE'];
      $center = $xmlArr['ATTRIBUTES']['CENTER'];
      $height = $xmlArr['ATTRIBUTES']['HEIGHT'];
      $angle = $xmlArr['ATTRIBUTES']['ANGLE'];
      $labelPos = $xmlArr['ATTRIBUTES']['LABELPOS'];
      $legendField = $xmlArr['ATTRIBUTES']['LAGENDFIELD'];
      
      if ($chartType == "Pie") {
         $plot = new PiePlot($data);
         $plot->SetLabelPos($labelPos);
      }
      else if ($chartType == "Pie3D") {
         $plot = new PiePlot3D($data);
         $plot->SetHeight($height);
         $plot->SetAngle($angle);
      }
      list($c1, $c2) = split(',', $center);
      $plot->SetCenter($c1,$c2); 
      $plot->SetSize($size); 

      $this->DrawString($plot->value,$xmlArr['VALUE']['ATTRIBUTES']['FONT'],$xmlArr['VALUE']['ATTRIBUTES']['COLOR']);

      return $plot;
   }
   
   /**
    * chartService::DrawString() - draw string
    * 
    * @param object $g plot object reference 
    * @access private
    * @return void
    */
   function DrawString(&$g,$font=null,$color=null)
   {
      if ($font) {
         list($ft,$fs,$size) = split(",",$font);
         $g->SetFont($this->GetFont($ft),$this->GetFontStyle($fs),$size);
      }
      if ($color) $g->SetColor($color);
   }
   
   /**
    * chartService::DrawLegend() - draw legend
    * 
    * @param object $g plot object reference 
    * @access private
    * @return void
    */
   function DrawLegend(&$g,$pos,$layout,$font,$color,$fcolor)
   {
      $this->DrawString($g,$font,$color);
      if ($fcolor) $g->SetFillColor($fcolor);
      if ($pos) {
         list($x,$y,$hap,$vap) = split(",",$pos);
         $g->SetPos($x,$y,$hap,$vap);
      }
      if ($layout && $layout == 'HOR') {
         $g->SetLayout(LEGEND_HOR);
      }
   }
   
   /**
    * chartService::DrawAxis() - draw legend
    * 
    * @param object $g plot object reference 
    * @access private
    * @return void
    */
   function DrawAxis(&$g,$labels,$font,$color,$labelAng,$title,$tfont,$tcolor,$tmargin)
   {
      $this->DrawString($g,$font, $color);
      if ($title) $g->title->Set($title); 
      $this->DrawString($g->title,$tfont, $tcolor);
      if ($labels) $g->SetTickLabels($labels);
      if ($labelAng) $g->SetLabelAngle($labelAng);
      if ($tmargin) $g->SetTitleMargin($tmargin);
   }
   
   /**
    * chartService::DrawMark() - draw legend
    * 
    * @param object $g plot object reference 
    * @access private
    * @return void
    */
   function DrawMark(&$g,$type,$color,$fcolor,$size)
   {
      if ($type) $g->SetType($this->GetMark($type));
      if ($color) $g->SetColor($color);
      if ($fcolor) $g->SetFillColor($fcolor);
      if ($size) $g->SetSize($size);
   }
   
   /**
    * chartService::GetMark() - get the point make number
    * 
    * @param string $mark point mark string
    * @access private
    * @return integer mark number
    */
   function GetMark($mark)
   {
      switch (strtoupper($mark)) {
         case "SQUARE": return MARK_SQUARE;
         case "UTRIANGLE": return MARK_UTRIANGLE;
         case "DTRIANGLE": return MARK_DTRIANGLE;
         case "DIAMOND": return MARK_DIAMOND;
         case "CIRCLE": return MARK_CIRCLE;
         case "FILLEDCIRCLE": return MARK_FILLEDCIRCLE;
         case "CROSS": return MARK_CROSS;
         case "STAR": return MARK_STAR;
         case "X": return MARK_X;
         default: return 0;
      }
   }
   
   /**
    * chartService::GetFont() - get the font number
    * 
    * @param string $mark font string
    * @access private
    * @return integer font number
    */
   function GetFont($font)
   {
      switch (strtoupper($font)) {
         case "ARIAL": return FF_ARIAL;
         case "COURIER;": return FF_COURIER;
         case "TIMES": return FF_TIMES;
         case "VERDANA": return FF_VERDANA;
         case "COMIC": return FF_COMIC;
         case "GEORGIA": return FF_GEORGIA;
         default: return FF_FONT1;
      }
   }
   /**
    * chartService::GetMark() - get the font style number
    * 
    * @param string $mark font style string
    * @access private
    * @return integer font style number
    */
   function GetFontStyle($fs)
   {
      switch (strtoupper($fs)) {
         case "NORMAL": return FS_NORMAL;
         case "BOLD": return FS_BOLD;
         case "ITALIC": return FS_ITALIC;
         case "BOLDITALIC": return FS_BOLDITALIC;
         default: return FS_NORMAL;
      }
   }
}

?>