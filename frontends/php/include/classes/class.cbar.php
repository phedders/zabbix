<?php
/*
** ZABBIX
** Copyright (C) 2000-2009 SIA Zabbix
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
**/
?>
<?php
class CBar extends CGraphDraw{

	public function __construct($type = GRAPH_TYPE_COLUMN){
		parent::__construct($type);

		$this->background = false;
		$this->opacity = 30;			// bar/column opacity
		$this->sum = false;

		$this->shiftlegendright = 0;	// count of static chars * px/char + for color rectangle + space
		$this->shiftCaption = 0;
		$this->maxCaption = 0;
		$this->drawlegendallow = 0;

		$this->series = array();

		$this->periodCaption = array();
		$this->seriesLegend = array();
		$this->seriesColor = array();
		$this->seriesCount = 0;
		$this->columnWidth = 10;					// bar/column width per serie
		$this->seriesWidth = 10;					// overal per serie bar/column width
		$this->seriesDistance = 10;

		$this->axisSideLeft = false;				// Do values for axis left/top persist
		$this->axisSideRight = false;				// Do values for axis right/bottom persist

		$this->xLabel = null;
		$this->yLabel = null;

		$this->yaxismin = array(GRAPH_YAXIS_SIDE_LEFT=>0, GRAPH_YAXIS_SIDE_RIGHT=>0);
		$this->yaxismax = array(GRAPH_YAXIS_SIDE_LEFT=>100, GRAPH_YAXIS_SIDE_RIGHT=>100);

		$this->minValue = array(GRAPH_YAXIS_SIDE_LEFT=>0, GRAPH_YAXIS_SIDE_RIGHT=>0);
		$this->maxValue = array(GRAPH_YAXIS_SIDE_LEFT=>null, GRAPH_YAXIS_SIDE_RIGHT=>null);

		$this->gridLinesCount = NULL;				// How many grids to draw
		$this->gridPixels = 40;						// optimal grid size
		$this->gridStep = array(GRAPH_YAXIS_SIDE_LEFT=>null, GRAPH_YAXIS_SIDE_RIGHT=>null);		// set value

		$this->side_values = array(GRAPH_YAXIS_SIDE_LEFT=>ITEM_VALUE_TYPE_UINT64,
									GRAPH_YAXIS_SIDE_RIGHT=>ITEM_VALUE_TYPE_UINT64);	// 0 - float, 3 - uint

		$this->column = null;

		$this->units = array(GRAPH_YAXIS_SIDE_LEFT=>'', GRAPH_YAXIS_SIDE_RIGHT=>'');	// Units for values
	}

	public function drawHeader(){
		$str=$this->header;
		$fontnum = ($this->sizeX < 500)?2:4;

		$x=$this->fullSizeX/2-imagefontwidth($fontnum)*strlen($str)/2;
		imagestring($this->im, $fontnum,$x,1, $str , $this->GetColor('Dark Red No Alpha'));
	}

	public function setGridStep($step,$axis=GRAPH_YAXIS_SIDE_LEFT){
		$this->gridStep[$axis] = $step;
	}

	public function setUnits($units,$axis=GRAPH_YAXIS_SIDE_LEFT){
		$this->units[$axis] = $units;
	}

	public function setSideValueType($type,$axis=GRAPH_YAXIS_SIDE_LEFT){
		$this->side_values[$axis] = $type;
	}

	public function showLegend($type=null){
		if(!is_null($type)){
			$this->drawlegendallow = $type;
			return $this->drawlegendallow;
		}
		else if($this->drawlegendallow == 0){
			$this->drawlegendallow = 1;
		}
		else {
			$this->drawlegendallow = 0;
		}

	return $this->drawlegendallow;
	}

	public function setXLabel($label){
		$this->xLabel = $label;
	}

	public function setYLabel($label){
		$this->yLabel = $label;
	}

	// SERIES SETTINGS
	public function addSeries($serie,$axis=GRAPH_YAXIS_SIDE_LEFT){
		if(GRAPH_YAXIS_SIDE_LEFT == $axis)	$this->axisSideLeft = true;
		else $this->axisSideRight = true;

		foreach($serie as $key => $value){
			$this->periodCaption[$key] = $key;

			if(!isset($this->series[$key])) $this->series[$key] = array();
			$this->series[$key][$this->seriesCount] = array('axis'=> $axis, 'value' => $value);
		}

		$this->seriesCount++;
	return $this->seriesCount;
	}

	public function setPeriodCaption($periodCaption){
		foreach($periodCaption as $key => $value){
			$this->periodCaption[$key] = $value;

			$tmp = zbx_strlen($value);
			if($tmp > $this->maxCaption) $this->maxCaption = $tmp;
		}
		$this->shiftCaption = round($this->maxCaption * 5.7);
	}

	public function setSeriesLegend($seriesLegend){
		foreach($seriesLegend as $key => $value){
			$this->seriesLegend[$key] = $value;

			$tmp = zbx_strlen($value) * 7 + 8;	// count of chars * font size + color box
			if($tmp > $this->shiftlegendright) $this->shiftlegendright = $tmp;
		}
	}

	public function setSeriesColor($seriesColor){
		foreach($seriesColor as $key => $value){
			$this->seriesColor[$key] = $value;
		}
	}

	protected function calcShifts(){
		$this->shiftXleft = 10 + (is_null($this->xLabel)?0:16);
		$this->shiftXright = 10;

		if($this->drawlegendallow == 0){
			$this->shiftlegendright = 0;
		}

		if($this->column){
			$this->shiftXCaptionLeft = ($this->axisSideLeft)?74:0;
			$this->shiftXCaptionRight = ($this->axisSideRight)?74:0;

			$this->shiftYCaptionTop = 0;
			$this->shiftYCaptionBottom = $this->shiftCaption;;
		}
		else{
			$this->shiftYCaptionTop = ($this->axisSideLeft)?74:0;
			$this->shiftYCaptionBottom = ($this->axisSideRight)?74:0;

			$this->shiftXCaptionLeft = $this->shiftCaption;
			$this->shiftXCaptionRight = 0;
		}

		$this->shiftYLegend =  0 + (is_null($this->yLabel)?0:16);
	}

	protected function calcSeriesWidth(){
		$serieLength = count($this->periodCaption);

		if($this->column){
			$seriesSizeX = $this->sizeX - ($this->seriesDistance * $serieLength);

			$this->columnWidth = floor($seriesSizeX / ($serieLength * $this->seriesCount));
			$this->seriesWidth = floor($seriesSizeX / $serieLength);
		}
		else{
			$seriesSizeY = $this->sizeY - ($this->seriesDistance * $serieLength);

			$this->columnWidth = floor($seriesSizeY / ($serieLength * $this->seriesCount));
			$this->seriesWidth = floor($seriesSizeY / $serieLength);
		}
	//SDI($this->columnWidth);
	}

	// Calculation of minimum Y axis
	protected function calcMiniMax(){
		if($this->stacked){
			for($i=0; $i < $this->seriesCount; $i++){
				$axis = GRAPH_YAXIS_SIDE_LEFT;
				$stackedMinValue = 0;
				$stackedMaxValue = 0;

				foreach($this->series as $key => $series){
					$value = $series[$i]['value'];

					if($value > 0)
						$stackedMaxValue+=$value;
					else
						$stackedMinValue+=$value;
				}


				if($this->minValue[$axis] > $stackedMinValue){
					$this->minValue[$axis] = $stackedMinValue;
				}

				if(($this->maxValue[$axis] < $stackedMaxValue) || is_null($this->maxValue[$axis])){
					$this->maxValue[$axis] = $stackedMaxValue;
				}
			}
		}
		else{
			foreach($this->series as $key => $series){
				foreach($series as $num => $serie){
					if($this->minValue[$serie['axis']] > $serie['value']){
						$this->minValue = $serie['value'];
					}

					if(($this->maxValue[$serie['axis']] < $serie['value']) || is_null($this->maxValue[$serie['axis']])){
						$this->maxValue[$serie['axis']] = $serie['value'];
					}
				}
			}
		}
	}

	protected function calcZero(){
		$left = GRAPH_YAXIS_SIDE_LEFT;
		$right = GRAPH_YAXIS_SIDE_RIGHT;

		$this->unit2px[$right] = ($this->m_maxY[$right] - $this->m_minY[$right])/$this->sizeY;
		$this->unit2px[$left] = ($this->m_maxY[$left] - $this->m_minY[$left])/$this->sizeY;

		if($this->m_minY[$right]>0){
			$this->zero[$right] = $this->sizeY+$this->shiftY;
			$this->oxy[$right] = min($this->m_minY[$right],$this->m_maxY[$right]);
		}
		else if($this->m_maxY[$right]<0) {
			$this->zero[$right] = $this->shiftY;
			$this->oxy[$right] = max($this->m_minY[$right],$this->m_maxY[$right]);
		}
		else{
			$this->zero[$right] = $this->sizeY+$this->shiftY - (int)abs($this->m_minY[$right]/$this->unit2px[$right]);
			$this->oxy[$right] = 0;
		}

		if($this->m_minY[$left]>0){
			$this->zero[$left] = $this->sizeY+$this->shiftY;
			$this->oxy[$left] = min($this->m_minY[$left],$this->m_maxY[$left]);
		}
		else if($this->m_maxY[$left]<0){
			$this->zero[$left] = $this->shiftY;
			$this->oxy[$left] = max($this->m_minY[$left],$this->m_maxY[$left]);
		}
		else{
			$this->zero[$left] = $this->sizeY+$this->shiftY - (int)abs($this->m_minY[$left]/$this->unit2px[$left]);
			$this->oxy[$left] = 0;
		}
	}

	protected function correctMiniMax(){
		$sides = array();
		if($this->axisSideLeft)	$sides[]=GRAPH_YAXIS_SIDE_LEFT;
		if($this->axisSideRight) $sides[]=GRAPH_YAXIS_SIDE_RIGHT;

		foreach($sides as $axis){

			if(is_null($this->gridStep[$axis])){
				if($this->column)
					$this->gridLinesCount = round($this->sizeY/$this->gridPixels) + 1;
				else
					$this->gridLinesCount = round($this->sizeX/$this->gridPixels) + 1;

				$tmp_maxY = $this->maxValue[$axis];
				$tmp_minY = $this->minValue[$axis];

				$maxValue = $this->maxValue[$axis];
				$minValue = $this->minValue[$axis];

	//SDI($this->minValue.' : '.$this->maxValue);
				if($this->side_values[$axis] == ITEM_VALUE_TYPE_UINT64){
					if($maxValue < $this->gridLinesCount) return true;

					$maxValue = round($maxValue);
					$minValue = floor($minValue);

					$value_delta = round($maxValue - $minValue);

					$step = floor((($value_delta/$this->gridLinesCount) + 1));	// round to top
					$value_delta2 = $step * $this->gridLinesCount;
	//SDI($value_delta.' <> '.$value_delta2);
					$first_delta = round(($value_delta2-$value_delta)/2);
					$second_delta = ($value_delta2-$value_delta) - $first_delta;

	//SDI($this->maxValue.' : '.$first_delta.' --- '.$this->minValue.' : '.$second_delta);
					if($minValue >= 0){
						if($minValue < $second_delta){
							$first_delta += $second_delta - $minValue;
							$second_delta = $minValue;
						}
					}
					else if(($maxValue <= 0)){
						if($maxValue > $first_delta){
							$second_delta += $first_delta - $maxValue;
							$first_delta = $maxValue;
						}
					}

					$maxValue += $first_delta;
					$minValue -= ($value_delta2-$value_delta) - $first_delta;
				}
	//SDI($minValue.' : '.$maxValue);
				else if($this->side_values == ITEM_VALUE_TYPE_FLOAT){
		//*
					if($maxValue>0){

						$maxValue = round($maxValue,1) + round($maxValue,1)*0.1 + 0.05;
					}
					else if($maxValue<0){
						$maxValue = round($maxValue,1) - round($maxValue,1)*0.1 + 0.05;
					}

					if($minValue>0){
						$minValue = $minValue - ($minValue * 0.2) - 0.05;
					}
					else if($minValue<0){
						$minValue = $minValue + ($minValue * 0.2) - 0.05;
					}

					$minValue = round($minValue,1);
		//*/
				}

				$this->minValue[$axis] = $minValue;
				$this->miaxValue[$axis] = $maxValue;
			}
			else{
				if(is_null($this->gridLinesCount))
					$this->gridLinesCount = floor($this->maxValue[$axis] / $this->gridStep[$axis]) + 1;
	// needs to be fixed!!!
	// via gridLinesCount can't be different for each axis,
	// due to this, gridStep must be some how normalised before calculations
				$this->maxValue[$axis] = $this->gridStep[$axis] * $this->gridLinesCount;
			}
		}
	}

	//***************************************************************************
	//									DRAW									*
	//***************************************************************************
	public function drawSmallRectangle(){
		dashedrectangle($this->im,
			$this->shiftXleft+$this->shiftXCaptionLeft-1,
			$this->shiftY-1+$this->shiftYCaptionTop,
			$this->sizeX+$this->shiftXleft+$this->shiftXCaptionLeft-1,
			$this->sizeY+$this->shiftY+1+$this->shiftYCaptionTop,
			$this->getColor('Black No Alpha')
			);
	}

	protected function drawGrid(){
		$this->drawSmallRectangle();

		if($this->column){
			$hline_count = $this->gridLinesCount;

			for($i=1;$i<$hline_count;$i++){
				dashedline($this->im,
						$this->shiftXleft+$this->shiftXCaptionLeft,
						$i*($this->sizeY/$hline_count)+$this->shiftY+$this->shiftYCaptionTop,
						$this->sizeX+$this->shiftXleft+$this->shiftXCaptionLeft,
						$i*($this->sizeY/$hline_count)+$this->shiftY+$this->shiftYCaptionTop,
						$this->getColor('Gray')
					);
			}

			$i=0;

			foreach($this->series as $key => $serie){
				$caption = $this->periodCaption[$key];
				$caption = str_pad($caption,$this->maxCaption,' ', STR_PAD_LEFT);

				imagestringup($this->im,
							1,
							$i*($this->seriesWidth+$this->seriesDistance)+$this->shiftXleft+$this->shiftXCaptionLeft+round($this->seriesWidth/2),
							$this->sizeY+$this->shiftY+$this->shiftYCaptionBottom,
							$caption,
							$this->getColor('Black No Alpha')
					);
				$i++;
			}

		}
		else{
			$vline_count = $this->gridLinesCount;

			for($i=1;$i<$vline_count;$i++){
				dashedline($this->im,
							$i*($this->sizeX/$vline_count)+$this->shiftXleft+$this->shiftXCaptionLeft,
							$this->shiftY+$this->shiftYCaptionTop,
							$i*($this->sizeX/$vline_count)+$this->shiftXleft+$this->shiftXCaptionLeft,
							$this->sizeY+$this->shiftY+$this->shiftYCaptionTop,
							$this->getColor('Gray')
					);
			}

			$i=0;

			foreach($this->series as $key => $serie){
				$caption = $this->periodCaption[$key];
				$caption = str_pad($caption,$this->maxCaption,' ', STR_PAD_LEFT);

				imagestring($this->im,
							1,
							$this->shiftXleft,
							($this->sizeY + $this->shiftY+$this->shiftYCaptionTop) - ($i*($this->seriesWidth+$this->seriesDistance)+$this->seriesDistance+round($this->seriesWidth/2)),
							$caption,
							$this->getColor('Black No Alpha')
					);
				$i++;
			}
		}

	}

	protected function drawSideValues(){
		$sides = array();
		if($this->axisSideLeft)	$sides[]=GRAPH_YAXIS_SIDE_LEFT;
		if($this->axisSideRight) $sides[]=GRAPH_YAXIS_SIDE_RIGHT;

		foreach($sides as $axis){

			$min = $this->minValue[$axis];
			$max = $this->maxValue[$axis];

			$hstr_count = $this->gridLinesCount;

			if($this->column){
				if(GRAPH_YAXIS_SIDE_LEFT == $axis){
					$shiftXLeft = $this->shiftXleft;
					$str_pad = STR_PAD_LEFT;
				}
				else{
					$shiftXLeft = $this->shiftXleft+$this->sizeX+$this->shiftXCaptionLeft+2;	// +2 because of some mistake somewhere in calculations! FIX IT!
					$str_pad = STR_PAD_RIGHT;
				}

				for($i=0;$i<=$hstr_count;$i++){
					$str = str_pad(convert_units(($this->sizeY*$i/$hstr_count*($max-$min)/$this->sizeY+$min),$this->units[$axis]),14,' ', $str_pad);
					imagestring($this->im,
								1,
								$shiftXLeft,
								$this->sizeY-$this->sizeY*$i/$hstr_count-4+$this->shiftY+$this->shiftYCaptionTop,
								$str,
								$this->getColor('Dark Red No Alpha')
							);
				}
			}
			else if(in_array($this->type, array(GRAPH_TYPE_BAR, GRAPH_TYPE_BAR_STACKED))){
				if(GRAPH_YAXIS_SIDE_LEFT == $axis){
					$shiftYBottom = $this->shiftY + $this->shiftYCaptionTop - 2; // -2 because of some mistake somewhere in calculations! FIX IT!
					$str_pad = STR_PAD_RIGHT;
				}
				else{
					$shiftYBottom = $this->shiftY + $this->sizeY + $this->shiftYCaptionTop + $this->shiftYCaptionBottom;
					$str_pad = STR_PAD_LEFT;
				}

				for($i=0;$i<=$hstr_count;$i++){
					$str = str_pad(convert_units(($this->sizeX*$i/$hstr_count*($max-$min)/$this->sizeX+$min),$this->units[$axis]),14,' ', $str_pad);

					imagestringup($this->im,
								1,
								$this->shiftXleft + ($this->sizeX*$i/$hstr_count-4)+$this->shiftXCaptionLeft,
								$shiftYBottom,
								$str,
								$this->getColor('Dark Red No Alpha')
							);
				}
			}
		}

		if(!is_null($this->xLabel)){
			imagestring($this->im, 2,
				$this->shiftXleft + ($this->sizeX/2) - 20,
				$this->fullSizeY-14,
				$this->xLabel,
				$this->getColor('Black No Alpha'));
		}

		if(!is_null($this->yLabel)){
			imagestringup($this->im, 2,
				0,
				$this->shiftY + ($this->sizeY/2) + 20,
				$this->yLabel,
				$this->getColor('Black No Alpha'));
		}
	}

	protected function drawLegend(){
		if(!$this->drawlegendallow) return;

		$shiftY = $this->shiftY;
		$shiftX = $this->fullSizeX - $this->shiftlegendright;

		$count = 0;

		foreach($this->series as $key => $serie){
			foreach($serie as $num => $value){
				$caption = $this->seriesLegend[$num];
				$color = $this->getColor($this->seriesColor[$num]);

				imagefilledrectangle($this->im, $shiftX, $shiftY+12*$count, $shiftX+5, $shiftY+5+12*$count, $color);
				imagerectangle($this->im,$shiftX, $shiftY+12*$count, $shiftX+5, $shiftY+5+12*$count, $this->getColor('Black No Alpha'));

				imagestring($this->im, 2,
					$shiftX+9,
					$shiftY-5+12*$count,
					$caption,
					$this->getColor('Black No Alpha'));

				$count++;
			}
			break;  //!!!!
		}
	}

	public function draw(){
		$start_time=getmicrotime();
		set_image_header();
		check_authorisation();

		$this->column = in_array($this->type, array(GRAPH_TYPE_COLUMN, GRAPH_TYPE_COLUMN_STACKED));

		$this->fullSizeX = $this->sizeX;
		$this->fullSizeY = $this->sizeY;

		if(($this->sizeX < 300) || ($this->sizeY < 200)) $this->switchlegend(0);

		$this->calcShifts();

		$this->sizeX -= ($this->shiftXleft+$this->shiftXright+$this->shiftlegendright+$this->shiftXCaptionLeft+$this->shiftXCaptionRight);
		$this->sizeY -= ($this->shiftY + $this->shiftYLegend + $this->shiftYCaptionBottom + $this->shiftYCaptionTop);

		$this->calcSeriesWidth();

		$this->calcMiniMax();
		$this->correctMiniMax();

	//	$this->calcZero();

		if(function_exists('imagecolorexactalpha') && function_exists('imagecreatetruecolor') && @imagecreatetruecolor(1,1))
			$this->im = imagecreatetruecolor($this->fullSizeX,$this->fullSizeY);
		else
			$this->im = imagecreate($this->fullSizeX,$this->fullSizeY);


		$this->initColors();
		$this->drawRectangle();
		$this->drawHeader();

		$this->drawGrid();
		$this->drawSideValues();

		$this->drawLogo();
		$this->drawLegend();

		$count = 0;
		if($this->column){
			$start = $this->shiftXleft+$this->shiftXCaptionLeft+floor($this->seriesDistance/2);
		}
		else{
			$start = $this->sizeY+$this->shiftY+$this->shiftYCaptionTop-floor($this->seriesDistance/2);
		}

	//	$start = ($this->column)?($this->shiftXleft + 1):($this->sizeY+$this->shiftY - 1);
		foreach($this->series as $key => $series){
			foreach($series as $num => $serie){
				$axis = $serie['axis'];
				$value = $serie['value'];

				$color = $this->getColor($this->seriesColor[$num],$this->opacity);
				if($this->column){
					imagefilledrectangle($this->im,
										$start,
										$this->sizeY+$this->shiftY+$this->shiftYCaptionTop - round(($this->sizeY/$this->maxValue[$axis]) * $value),
										$start+$this->columnWidth,
										$this->sizeY+$this->shiftY+$this->shiftYCaptionTop,
										$color);

					imagerectangle($this->im,
										$start,
										$this->sizeY+$this->shiftY+$this->shiftYCaptionTop - round(($this->sizeY/$this->maxValue[$axis]) * $value),
										$start+$this->columnWidth,
										$this->sizeY+$this->shiftY+$this->shiftYCaptionTop,
										$this->getColor('Black No Alpha'));
				}
				else{
					imagefilledrectangle($this->im,
										$this->shiftXleft+$this->shiftXCaptionLeft,
										$start-$this->columnWidth,
										$this->shiftXleft+$this->shiftXCaptionLeft + round(($this->sizeX/$this->maxValue[$axis]) * $value),
										$start,
										$color);


					imagerectangle($this->im,
										$this->shiftXleft+$this->shiftXCaptionLeft,
										$start-$this->columnWidth,
										$this->shiftXleft+$this->shiftXCaptionLeft + round(($this->sizeX/$this->maxValue[$axis]) * $value),
										$start,
										$this->getColor('Black No Alpha'));
				}
				$start=($this->column)?($start+$this->columnWidth):($start-$this->columnWidth);
			}

			$count++;
			if($this->column){
				$start=$count*($this->seriesWidth+$this->seriesDistance)+$this->shiftXleft+$this->shiftXCaptionLeft+floor($this->seriesDistance/2);
			}
			else{
				$start=($this->sizeY + $this->shiftY+$this->shiftYCaptionTop) - ($count*($this->seriesWidth+$this->seriesDistance)) - floor($this->seriesDistance/2);
			}
		}

		$end_time=getmicrotime();
		$str=sprintf('%0.2f',(getmicrotime()-$start_time));
		imagestring($this->im, 0,$this->fullSizeX-120,$this->fullSizeY-12,"Generated in $str sec", $this->GetColor('Gray'));

		unset($this->items, $this->data);

		imageOut($this->im);
	}
}
?>
