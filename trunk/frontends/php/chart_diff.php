<? 
	include "include/config.inc.php";

#	PARAMETERS:
	
#	itemid
#	period
#	from

	if(!isset($period))
	{
		$period=0;
	}

	if(!isset($from))
	{
		$from=0;
	}

	$sizeX=900;
	$sizeY=200;

	$shiftX=10;
	$shiftY=13;

	$nodata=1;	


//	Header( "Content-type:  text/html"); 
	Header( "Content-type:  image/png"); 
	Header( "Expires:  Mon, 17 Aug 1998 12:51:50 GMT"); 

	$im = imagecreate($sizeX+$shiftX+61,$sizeY+2*$shiftY+10); 
  
	$red=ImageColorAllocate($im,255,0,0); 
	$green=ImageColorAllocate($im,0,255,0); 
	$darkgreen=ImageColorAllocate($im,0,150,0); 
	$blue=ImageColorAllocate($im,0,0,255); 
	$yellow=ImageColorAllocate($im,255,255,0); 
	$cyan=ImageColorAllocate($im,0,255,255); 
	$black=ImageColorAllocate($im,0,0,0); 
	$gray=ImageColorAllocate($im,150,150,150); 

	$x=imagesx($im); 
	$y=imagesy($im);
  
	ImageFilledRectangle($im,0,0,$sizeX+$shiftX+61,$sizeY+2*$shiftY+10,$black);

	for($i=0;$i<=$sizeY;$i+=50)
	{
		ImageDashedLine($im,$shiftX,$i+$shiftY,$sizeX+$shiftX,$i+$shiftY,$darkgreen);
	}
	for($i=0;$i<=$sizeX;$i+=50)
	{
		ImageDashedLine($im,$i+$shiftX,$shiftY,$i+$shiftX,$sizeY+$shiftY,$darkgreen);
	}

	$from_time = time(NULL)-$period-3600*$from;
	$to_time   = time(NULL)-3600*$from;
	$result=DBselect("select count(clock),min(clock),max(clock),min(value),max(value) from history where itemid=$itemid and clock>$from_time and clock<$to_time ");
	$count=DBget_field($result,0,0);
	if($count>0)
	{
		$nodata=0;
		$minX=DBget_field($result,0,1);
		$maxX=DBget_field($result,0,2);
		$minY=DBget_field($result,0,3);
		$maxY=DBget_field($result,0,4);
		
	}
	else
	{
		unset($maxX);
		unset($maxY);
		unset($minX);
		unset($minY);
	}

	$minY=0;
	$maxY=0.0001;
//	$minX=0;
//	$maxX=1000;

	
//	echo "MIN/MAX:",$minX," - ",$maxX," - ",$minY," - ",$maxY,"<Br>";

	$result=DBselect("select clock,value from history where itemid=$itemid and clock>$from_time and clock<$to_time order by clock");
	if(isset($minX)&&($minX!=$maxX)&&($minY!=$maxY))
	{
		for($i=0;$i<DBnum_rows($result)-3;$i++)
		{
			$x=DBget_field($result,$i,0);
			$x_next=DBget_field($result,$i+1,0);
			$x_next_next=DBget_field($result,$i+2,0);
			$y=DBget_field($result,$i,1);
			$y_next=DBget_field($result,$i+1,1);
			$y_next_next=DBget_field($result,$i+2,1);

			if(!isset($minY)||($y_next-$y<$minY))
			{
				$minY=$y_next-$y;
			}	
			if(!isset($maxY)||($y_next-$y>$maxY))
			{
				$maxY=$y_next-$y;
			}	
		}
		$y1=$sizeY*(-$minY)/($maxY-$minY);
		$y1=$sizeY-$y1;
		ImageDashedLine($im,$shiftX,$y1+$shiftY,$sizeX+$shiftX,$y1+$shiftY,$yellow);
		for($i=0;$i<DBnum_rows($result)-3;$i++)
		{
			$x=DBget_field($result,$i,0);
			$x_next=DBget_field($result,$i+1,0);
			$x_next_next=DBget_field($result,$i+2,0);
			$y=DBget_field($result,$i,1);
			$y_next=DBget_field($result,$i+1,1);
			$y_next_next=DBget_field($result,$i+2,1);

			$x1=$sizeX*($x-$minX)/($maxX-$minX);
			$y1=$sizeY*(($y_next-$y)-$minY)/($maxY-$minY);
			$x2=$sizeX*($x_next-$minX)/($maxX-$minX);
			$y2=$sizeY*(($y_next_next-$y_next)-$minY)/($maxY-$minY);

			$y1=$sizeY-$y1;
			$y2=$sizeY-$y2;

//		echo $x1," - ",$x2," - ",$y1," - ",$y2,"<Br>";
			ImageLine($im,$x1+$shiftX,$y1+$shiftY,$x2+$shiftX,$y2+$shiftY,$green);
		}
	}
	else
	{
		if(isset($minX))
		{
			ImageLine($im,$shiftX,$shiftY+$sizeY/2,$sizeX+$shiftX,$shiftY+$sizeY/2,$green);
		}
	}

	if($nodata == 0)
	{
		for($i=0;$i<=$sizeY;$i+=50)
		{
			ImageString($im, 1, $sizeX+5+$shiftX, $sizeY-$i-4+$shiftY, $i*($maxY-$minY)/$sizeY+$minY , $red);
		}

		ImageString($im, 1,10,                $sizeY+$shiftY+5, date("dS of F Y h:i:s A",$minX) , $red);
		ImageString($im, 1,$sizeX+$shiftX-168,$sizeY+$shiftY+5, date("dS of F Y h:i:s A",$maxX) , $red);
	}
	else
	{
		ImageString($im, 2,$sizeX/2-50,                $sizeY+$shiftY+3, "NO DATA FOUND FOR THIS PERIOD" , $red);
	}

	ImageStringUp($im,0,2*$shiftX+$sizeX+40,$sizeY+2*$shiftY, "http://zabbix.sourceforge.net", $gray);

	ImagePng($im); 
	ImageDestroy($im); 
?>
