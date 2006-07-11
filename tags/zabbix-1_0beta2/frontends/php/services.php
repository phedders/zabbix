<?
	$page["title"] = "High-level representation of monitored data";
	$page["file"] = "services.php";

	include "include/config.inc";
	show_header($page["title"],0,0);
?>

<?
	if(isset($register))
	{
		if($register=="update")
		{
			$result=@update_service($serviceid,$name,$triggerid,$linktrigger);
			show_messages($result,"Service updated","Cannot update service");
		}
		if($register=="add")
		{
			$result=@add_service($name,$triggerid,$linktrigger);
			show_messages($result,"Service added","Cannot add service");
		}
		if($register=="add link")
		{
			if(!isset($softlink))
			{
				$softlink=0;
			}
			$result=add_service_link($servicedownid,$serviceupid,$softlink);
			show_messages($result,"Service link added","Cannot add service link");
		}
		if($register=="delete")
		{
			$result=delete_service($serviceid);
			show_messages($result,"Service deleted","Cannot delete service");
			unset($serviceid);
		}
	}
?>

<?
	show_table_header("IT SERVICES");

	echo "<br>";

	show_table_header("SERVICES");
?>
<?
	$now=time();
	$result=DBselect("select serviceid,name from services order by name");
	echo "<table border=0 width=100% bgcolor='#CCCCCC' cellspacing=1 cellpadding=3>";
	echo "<tr>";
	echo "<td><b>Service</b></td>";
	echo "<td width=\"10%\"><b>Actions</b></td>";
	echo "</tr>";

	$col=0;
	if(isset($serviceid))
	{
		echo "<tr bgcolor=#EEEEEE>";
		$service=get_service_by_serviceid($serviceid);
		echo "<td><b><a href=\"services.php?serviceid=".$service["serviceid"]."#form\">".$service["name"]."</a></b></td>";
		echo "<td><a href=\"services.php?serviceid=".$service["serviceid"]."&register=delete\">Delete</a></td>";
		echo "</tr>"; 
		$col++;
	}
	while($row=DBfetch($result))
	{
		if(!isset($serviceid) && service_has_parent($row["serviceid"]))
		{
			continue;
		}
		if(isset($serviceid) && service_has_no_this_parent($serviceid,$row["serviceid"]))
		{
			continue;
		}
		if(isset($serviceid)&&($serviceid==$row["serviceid"]))
		{
			echo "<tr bgcolor=#99AABB>";
		}
		else
		{
			if($col++%2==0)	{ echo "<tr bgcolor=#EEEEEE>"; }
			else		{ echo "<tr bgcolor=#DDDDDD>"; }
		}
		$childs=get_num_of_service_childs($row["serviceid"]);
		if(isset($serviceid))
		{
			echo "<td><a href=\"services.php?serviceid=".$row["serviceid"]."#form\"> - ".$row["name"]." [$childs]</a></td>";
		}
		else
		{
			echo "<td><a href=\"services.php?serviceid=".$row["serviceid"]."#form\">".$row["name"]." [$childs]</a></td>";
		}
		echo "<td><a href=\"services.php?serviceid=".$row["serviceid"]."&register=delete\">Delete</a></td>";
		echo "</tr>";
	}
	echo "</table>";
?>

<?
	if(isset($serviceid))
	{
		$result=DBselect("select serviceid,triggerid,name from services where serviceid=$serviceid");
		$triggerid=DBget_field($result,0,1);
		$name=DBget_field($result,0,2);
	}
	else
	{
		$name="";
		unset($triggerid);
	}

	echo "<br>";
	echo "<a name=\"form\"></a>";
	show_table2_header_begin();
	echo "New service";

	show_table2_v_delimiter();
	echo "<form method=\"post\" action=\"services.php\">";
	if(isset($serviceid))
	{
		echo "<input name=\"serviceid\" type=\"hidden\" value=$serviceid>";
	}
	echo "Name";
	show_table2_h_delimiter();
	echo "<input name=\"name\" value=\"$name\" size=32>";

        show_table2_v_delimiter();
        echo "Link to trigger ?";
        show_table2_h_delimiter();
	if(isset($triggerid)&&($triggerid!=""))
	{
        	echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"linktrigger\" VALUE=\"true\" CHECKED>";
	}
	else
	{
        	echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"linktrigger\">";
	}

	show_table2_v_delimiter();
	echo "Trigger";
	show_table2_h_delimiter();
        $result=DBselect("select triggerid,description from triggers order by description");
        echo "<select name=\"triggerid\" size=1>";
        for($i=0;$i<DBnum_rows($result);$i++)
        {
                $triggerid_=DBget_field($result,$i,0);
                $description_=DBget_field($result,$i,1);
                if(isset($triggerid) && ($triggerid==$triggerid_))
                {
                        echo "<OPTION VALUE='$triggerid_' SELECTED>$description_";
                }
                else
                {
                        echo "<OPTION VALUE='$triggerid_'>$description_";
                }
        }
        echo "</SELECT>";
	show_table2_v_delimiter2();
	echo "<input type=\"submit\" name=\"register\" value=\"add\">";
	if(isset($serviceid))
	{
		echo "<input type=\"submit\" name=\"register\" value=\"update\">";
	}

	show_table2_header_end();
?>

<?
	if(isset($serviceid))
	{
		$result=DBselect("select serviceid,triggerid,name from services where serviceid=$serviceid");
		$triggerid=DBget_field($result,0,1);
		$name=DBget_field($result,0,2);
	}
	else
	{
		$name="";
		unset($triggerid);
	}

	echo "<br>";
	show_table2_header_begin();
	echo "New link";

	show_table2_v_delimiter();
	echo "<form method=\"post\" action=\"services.php\">";
	if(isset($serviceid))
	{
		echo "<input name=\"serviceid\" type=\"hidden\" value=$serviceid>";
		echo "<input name=\"serviceupid\" type=\"hidden\" value=$serviceid>";
	}
	echo "Name";
	show_table2_h_delimiter();
	$result=DBselect("select serviceid,triggerid,name from services order by name");
        echo "<select name=\"servicedownid\" size=1>";
        for($i=0;$i<DBnum_rows($result);$i++)
        {
                $servicedownid_=DBget_field($result,$i,0);
                $name_=DBget_field($result,$i,2);
		echo "<OPTION VALUE='$servicedownid_'>$name_";
        }
        echo "</SELECT>";

        show_table2_v_delimiter();
        echo "Soft link ?";
        show_table2_h_delimiter();
	if(isset($softlink)&&($triggerid!=""))
	{
        	echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"softlink\" VALUE=\"true\">";
	}
	else
	{
        	echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"softlink\">";
	}

	show_table2_v_delimiter2();
	echo "<input type=\"submit\" name=\"register\" value=\"add link\">";

	show_table2_header_end();
?>

<?
	show_footer();
?>