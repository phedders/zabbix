<?
	$page["title"] = "Media";
	$page["file"] = "media.php";

	include "include/config.inc";
	show_header($page["title"],0,0);
?>

<?
	show_table_header("MEDIA");
?>

<?
	if(isset($register))
	{
		if($register=="enable")
		{
			$result=activate_media( $mediaid );
			show_messages($result,"Media activated","Cannot activate media");
		}
		elseif($register=="disable")
		{
			$result=disactivate_media( $mediaid );
			show_messages($result,"Media disabled","Cannot disable media");
		}
		elseif($register=="add")
		{
			$result=add_media( $userid, $type, $sendto);
			show_messages($result,"Media added","Cannot add media");
		}
		elseif($register=="delete")
		{
			$result=delete_media( $mediaid );
			show_messages($result,"Media deleted","Cannot delete media");
			unset($mediaid);
		}
	}
?>


<FONT COLOR="#000000">
<?
	$sql="select mediaid,type,sendto,active from media where userid=$userid order by type,sendto";
	$result=DBselect($sql);

	echo "<CENTER>";
	echo "<TABLE BORDER=0 WIDTH=\"100%\" BGCOLOR=\"#CCCCCC\" cellspacing=1 cellpadding=3>";
	echo "<TR>";
	echo "<TD><B>Type</B></TD>";
	echo "<TD><B>Send to</B></TD>";
	echo "<TD><B>Status</B></TD>";
	echo "<TD><B>Actions</B></TD>";
	echo "</TR>";

	$col=0;
	for($i=0;$i<DBnum_rows($result);$i++)
	{
		if($col==1)
		{
			echo "<TR BGCOLOR=#DDDDDD>";
			$col=0;
		} else
		{
			echo "<TR BGCOLOR=#EEEEEE>";
			$col=1;
		}
		$mediaid=DBget_field($result,$i,0);
		$type_=DBget_field($result,$i,1);
		echo "<TD>";
		if($type_=="EMAIL")
		{
			echo "E-mail";
		}
		else
		{
			echo $type_;
		}
		echo "</TD>";
		echo "<TD>",DBget_field($result,$i,2),"</TD>";
		echo "<TD>";
		if(DBget_field($result,$i,3)==0) 
		{
			echo "<a href=\"media.php?register=disable&mediaid=$mediaid&userid=$userid\">Enabled</A>";
		}
		else
		{
			echo "<a href=\"media.php?register=enable&mediaid=$mediaid&userid=$userid\">Disabled</A>";
		}
		echo "</TD>";
		echo "<TD>";
		echo "<A HREF=\"media.php?register=delete&mediaid=$mediaid&userid=$userid\">Delete</A>";
		echo "</TD>";
		echo "</TR>";
	}
	echo "</TABLE>";
?>
</FONT>
</TR>
</TABLE></CENTER>

<CENTER>
<?
	echo "<br>";
	show_table2_header_begin();
	echo "New media";

	show_table2_v_delimiter();
	echo "<form method=\"post\" action=\"media.php\">";
	echo "<input name=\"userid\" type=\"hidden\" value=$userid>";
	echo "Type";
	show_table2_h_delimiter();
	echo "<select name=\"type\" size=1>";
	echo "<OPTION VALUE=\"EMAIL\">E-mail";
	echo "</SELECT>";

	show_table2_v_delimiter();
	echo "Send to";
	show_table2_h_delimiter();
	echo "<input name=\"sendto\" size=20>";

	show_table2_v_delimiter2();
	echo "<input type=\"submit\" name=\"register\" value=\"add\">";

	show_table2_header_end();

	show_footer();
?>
