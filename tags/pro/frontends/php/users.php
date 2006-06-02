<?php
	$page["title"] = "Users";
	$page["file"] = "users.php";

	include "include/config.inc.php";
	show_header($page["title"],0,0);
?>

<?php
        if(!check_right("User","U",0))
        {
                show_table_header("<font color=\"AA0000\">No permissions !</font
>");
                show_footer();
                exit;
        }
?>

<?php
	if(isset($HTTP_GET_VARS["register"]))
	{
		if($HTTP_GET_VARS["register"]=="add")
		{
			if($HTTP_GET_VARS["password1"]==$HTTP_GET_VARS["password2"])
			{
				$result=add_user($HTTP_GET_VARS["name"],$HTTP_GET_VARS["surname"],$HTTP_GET_VARS["alias"],$HTTP_GET_VARS["password1"]);
				show_messages($result, "User added", "Cannot add user");
			}
			else
			{
				show_error_message("Cannot add user. Both passwords must be equal.");
			}
		}
		if($HTTP_GET_VARS["register"]=="delete")
		{
			$result=delete_user($HTTP_GET_VARS["userid"]);
			show_messages($result, "User successfully deleted", "Cannot delete user");
			unset($userid);
		}
		if($HTTP_GET_VARS["register"]=="delete_permission")
		{
			$result=delete_permission($HTTP_GET_VARS["rightid"]);
			show_messages($result, "Permission successfully deleted", "Cannot delete permission");
			unset($rightid);
		}
		if($HTTP_GET_VARS["register"]=="add permission")
		{
			$result=add_permission($HTTP_GET_VARS["userid"],$HTTP_GET_VARS["right"],$HTTP_GET_VARS["permission"],$HTTP_GET_VARS["id"]);
			show_messages($result, "Permission successfully added", "Cannot add permission");
		}
		if($HTTP_GET_VARS["register"]=="update")
		{
			if($HTTP_GET_VARS["password1"]==$HTTP_GET_VARS["password2"])
			{
				$result=update_user($HTTP_GET_VARS["userid"],$HTTP_GET_VARS["name"],$HTTP_GET_VARS["surname"],$HTTP_GET_VARS["alias"],$HTTP_GET_VARS["password1"]);
				show_messages($result, "Information successfully updated", "Cannot update information");
			}
			else
			{
				show_error_message("Cannot update user. Both passwords must be equal.");
			}
		}
	}
?>

<?php
	show_table_header("CONFIGURATION OF USERS");
?>

<?php
	echo "<TABLE BORDER=0 COLS=4 align=center WIDTH=100% BGCOLOR=\"#CCCCCC\" cellspacing=1 cellpadding=3>";
	echo "<TR><TD WIDTH=3%><B>Id</B></TD>";
	echo "<TD WIDTH=10%><B>Alias</B></TD>";
	echo "<TD WIDTH=10%><B>Name</B></TD>";
	echo "<TD WIDTH=10%><B>Surname</B></TD>";
	echo "<TD WIDTH=10%><B>Actions</B></TD>";
	echo "</TR>";

	$result=DBselect("select u.userid,u.alias,u.name,u.surname from users u order by u.alias");
	$col=0;
	while($row=DBfetch($result))
	{
		if(!check_right("User","R",$row["userid"]))
		{
			continue;
		}
		if($col++%2==0)	{ echo "<TR BGCOLOR=#EEEEEE>"; }
		else		{ echo "<TR BGCOLOR=#DDDDDD>"; }
	
		echo "<TD>".$row["userid"]."</TD>";
		echo "<TD>".$row["alias"]."</TD>";
		echo "<TD>".$row["name"]."</TD>";
		echo "<TD>".$row["surname"]."</TD>";
		echo "<TD>";
        	if(check_right("User","U",$row["userid"]))
		{
			if(get_media_count_by_userid($row["userid"])>0)
			{
				echo "<A HREF=\"users.php?register=change&userid=".$row["userid"]."#form\">Change</A> - <A HREF=\"media.php?userid=".$row["userid"]."\"><b>M</b>edia</A>";
			}
			else
			{
				echo "<A HREF=\"users.php?register=change&userid=".$row["userid"]."#form\">Change</A> - <A HREF=\"media.php?userid=".$row["userid"]."\">Media</A>";
			}
		}
		else
		{
			echo "Change - Media";
		}
		echo "</TD>";
		echo "</TR>";
	}
	echo "</TABLE>";
?>

<?php
	if(isset($HTTP_GET_VARS["userid"]))
	{
	echo "<br>";
	echo "<a name=\"form\"></a>";
	show_table_header("USER PERMISSIONS");
	echo "<TABLE BORDER=0 align=center COLS=4 WIDTH=100% BGCOLOR=\"#CCCCCC\" cellspacing=1 cellpadding=3>";
	echo "<TR><TD WIDTH=10%><B>Permission</B></TD>";
	echo "<TD WIDTH=10%><B>Right</B></TD>";
	echo "<TD WIDTH=10% NOSAVE><B>Resource name</B></TD>";
	echo "<TD WIDTH=10% NOSAVE><B>Actions</B></TD>";
	echo "</TR>";
	$result=DBselect("select rightid,name,permission,id from rights where userid=".$HTTP_GET_VARS["userid"]." order by name,permission,id");
	$col=0;
	while($row=DBfetch($result))
	{
//        	if(!check_right("User","R",$row["userid"]))
//		{
//			continue;
//		}
		if($col++%2==0)	{ echo "<TR BGCOLOR=#EEEEEE>"; }
		else		{ echo "<TR BGCOLOR=#DDDDDD>"; }
	
		echo "<TD>".$row["name"]."</TD>";
		if($row["permission"]=="R")
		{
			echo "<TD>Read only</TD>";
		}
		else if($row["permission"]=="U")
		{
			echo "<TD>Read-write</TD>";
		}
		else if($row["permission"]=="H")
		{
			echo "<TD>Hide</TD>";
		}
		else if($row["permission"]=="A")
		{
			echo "<TD>Add</TD>";
		}
		else
		{
			echo "<TD>".$row["permission"]."</TD>";
		}
		echo "<TD>".get_resource_name($row["name"],$row["id"])."</TD>";
		echo "<TD><A HREF=users.php?userid=".$HTTP_GET_VARS["userid"]."&rightid=".$row["rightid"]."&register=delete_permission>Delete</A></TD>";
	}
	echo "</TR>";
	echo "</TABLE>";

	insert_permissions_form($HTTP_GET_VARS["userid"]);

	}
?>

<?php
	echo "<br>";

	@insert_user_form($HTTP_GET_VARS["userid"]);
?>

<?php
	show_footer();
?>