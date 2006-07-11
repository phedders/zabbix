<?php
	$page["title"] = "Alarms";
	$page["file"] = "alarms.php";

	include "include/config.inc.php";
	show_header($page["title"],0,0);
?>

<?php
	if(!check_right_on_trigger("R",$HTTP_GET_VARS["triggerid"]))
        {
                show_table_header("<font color=\"AA0000\">No permissions !</font
>");
                show_footer();
                exit;
        }
?>

<?php
	show_table_header_begin();
	echo "ALARMS";
 
	show_table_v_delimiter(); 

	if(!isset($HTTP_GET_VARS["triggerid"]))
	{
		echo "<div align=center><B>No triggerID!!!!</B><BR>Please Contact Server Adminstrator</div>";
		show_footer();
		exit;
	}
	else
	{
		$trigger=get_trigger_by_triggerid($HTTP_GET_VARS["triggerid"]);

		$Expression=$trigger["expression"];
		$Description=$trigger["description"];
//		$Priority=$trigger["priority"];
	}
?>

<?php
	if(isset($HTTP_GET_VARS["limit"]) && ($HTTP_GET_VARS["limit"]=="NO"))
	{
		echo "[<A HREF=\"alarms.php?triggerid=".$HTTP_GET_VARS["triggerid"]."&limit=30\">";
		echo "Show only last 100</A>]";
		$limit=" ";
	}
	else 
	{
		echo "[<A HREF=\"alarms.php?triggerid=".$HTTP_GET_VARS["triggerid"]."&limit=NO\">";
		echo "Show all</A>]";
		$limit=" limit 100";
	}

	show_table_header_end();
	echo "<br>";
?>


<?php
	$Expression=explode_exp($Expression,1);
	if( strstr($Description,"%s"))
	{
		$Description=expand_trigger_description($trigger["triggerid"]);
	}
	show_table_header("$Description<BR><font size=-1>$Expression</font>");
?>

<FONT COLOR="#000000">
<?php
	$sql="select clock,value,triggerid from alarms where triggerid=".$HTTP_GET_VARS["triggerid"]." order by clock desc $limit";
	$result=DBselect($sql);

	echo "<TABLE WIDTH=100% align=center BORDER=0 BGCOLOR=\"#CCCCCC\" cellspacing=1 cellpadding=3>";
	echo "<TR>";
	echo "<TD><B>Time</B></TD>";
	echo "<TD><B>Status</B></TD>";
	echo "<TD><B>Duration</B></TD>";
	echo "<TD><B>Sum</B></TD>";
	echo "<TD><B>%</B></TD>";
	echo "</TR>";
	$truesum=0;
	$falsesum=0;
	$dissum=0;
	$clock=mktime();
	while($row=DBfetch($result))
	{
		$lclock=$clock;
		$clock=$row["clock"];
		$leng=$lclock-$row["clock"];

		if($row["value"]==0)		{ echo "<TR BGCOLOR=#EEFFEE>"; }
		elseif($row["value"]==2)	{ echo "<TR BGCOLOR=#EEEEEE>"; }
		else				{ echo "<TR BGCOLOR=#FFDDDD>"; }

		echo "<TD>",date("Y.M.d H:i:s",$row["clock"]),"</TD>";
		if($row["value"]==1)
		{
			$istrue="TRUE";
			$truesum=$truesum+$leng;
			$sum=$truesum;
		}
		elseif($row["value"]==0)
		{
			$istrue="FALSE";
			$falsesum=$falsesum+$leng;
			$sum=$falsesum;
		}
		elseif($row["value"]==3)
		{
			$istrue="DISABLED";
			$dissum=$dissum+$leng;
			$sum=$dissum;
		}
		elseif($row["value"]==2)
		{
			$istrue="UNKNOWN";
			$dissum=$dissum+$leng;
			$sum=$dissum;
		}
	
		$proc=(100*$sum)/($falsesum+$truesum+$dissum);
		$proc=round($proc*100)/100;
		$proc="$proc%";
 
		echo "<TD><B>",$istrue,"</B></TD>";
		if($leng>60*60*24)
		{
			$leng= round(($leng/(60*60*24))*10)/10;
			$leng="$leng days";
		}
		elseif ($leng>60*60)
		{
			$leng= round(($leng/(60*60))*10)/10;
			$leng="$leng hours"; 
		}
		elseif ($leng>60)
		{
			$leng= round(($leng/(60))*10)/10;
			$leng="$leng mins";
		}
		else
		{
			$leng="$leng secs";
		}

		if($sum>60*60*24)
		{
			$sum= round(($sum/(60*60*24))*10)/10;
			$sum="$sum days";
		}
		elseif ($sum>60*60)
		{
			$sum= round(($sum/(60*60))*10)/10;
			$sum="$sum hours"; 
		}
		elseif ($sum>60)
		{
			$sum= round(($sum/(60))*10)/10;
			$sum="$sum mins";
		}
		else
		{
			$sum="$sum secs";
		}
  
		echo "<TD>$leng</TD>";
		echo "<TD>$sum</TD>";
		echo "<TD>$proc</TD>";
		echo "</TR>";
	}
	echo "</TABLE>";
?>

<?php
	show_footer();
?>