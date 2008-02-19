<?php /*dotmg modifications : contact m.randimbisoa@dotmg.net*/ ?>
<div class="page">
<?php
if ($this->HasAccess("read") || $this->IsAdmin())
{
	if (!$this->page)
	{
		print("<p>This page doesn't exist yet. Maybe you want to <a href=\"".$this->href("edit")."\">create</a> it?</p>");
	}
	else
	{
		// comment header?
		if ($this->page["comment_on"])
		{
			print("<div class=\"commentinfo\">This is a comment on ".$this->Link($this->page["comment_on"], "", "", 0).", posted by ".$this->Format($this->page["user"])." at ".$this->page["time"]."</div>");
		}

		if ($this->page["latest"] == "N")
		{
			print("<div class=\"revisioninfo\">This is an old revision of <a href=\"".$this->href()."\">".$this->GetPageTag()."</a> from ".$this->page["time"].".</div>");
		}


		// display page
		print($this->Format($this->page["body"], "wakka"));

		// if this is an old revision, display some buttons
		if ($this->HasAccess("write") && ($this->page["latest"] == "N"))
		{
			//#dotmg [1 line modified, 2 lines added, 7 lines indented]: added if encapsulation : in case where some pages were brutally deleted from database
			if ($latest = $this->LoadPage($this->tag))
			{
				?>
 				<?php echo $this->FormOpen("edit") ?>
 				<input type="hidden" name="previous" value="<?php echo $latest["id"] ?>" />
 				<input type="hidden" name="body" value="<?php echo htmlspecialchars($this->page["body"]) ?>" />
 				<input type="submit" value="Re-edit this old revision" />
 				<?php echo $this->FormClose(); ?>
 				<?php
			}
		}
	}
}
else
{
	print("<p><em>You aren't allowed to read this page.</em></p>");
}
?>

</div>


<?php
if ($this->HasAccess("read") && $this->GetConfigValue("hide_comments") != 1)
{
	// load comments for this page
	$comments = $this->LoadComments($this->tag);

	// store comments display in session
	$tag = $this->GetPageTag();
	if (!isset($_SESSION["show_comments"][$tag]))
		$_SESSION["show_comments"][$tag] = ($this->UserWantsComments() ? "1" : "0");
	if (isset($_REQUEST["show_comments"])){	
	switch($_REQUEST["show_comments"])
	{
	case "0":
		$_SESSION["show_comments"][$tag] = 0;
		break;
	case "1":
		$_SESSION["show_comments"][$tag] = 1;
		break;
	}
	}
	// display comments!
	if ($this->page && $_SESSION["show_comments"][$tag])
	{
		// display comments header
		?>
<div class="commentsheader">
<span id="comments">&nbsp;</span>Comments [<a href="<?php echo $this->href("", "", "show_comments=0") ?>">Hide comments/form</a>]
</div>
		<?php
		// display comments themselves
		if ($comments)
		{
            	// $current_user=$this->GetUser();
			$current_user = $this->GetUserName(); 
 			foreach ($comments as $comment)
			{
				print("<div class=\"comment\">\n");
				print("<span id=\"".$comment["tag"]."\">&nbsp;</span>".$this->Format($comment["body"])."\n");
				print("\t<div class=\"commentinfo\">\n-- ".$this->Format($comment["user"])." (".$comment["time"].")");
				$current_user = $this->GetUserName(); 
				// if ($current_user=$this->GetUser()) {
          				// if ($this->UserIsOwner() || $current_user['name'] == $comment["owner"] || $this->IsAdmin())
          				if ($this->UserIsOwner() || $current_user == $comment["owner"] || ($this->config['anony_delete_own_comments'] && $current_user == $comment["user"]) )
					{
						?>
						<?php echo $this->FormOpen("delcomment"); ?>
						<input type="hidden" name="comment_number" value="<?php echo $comment["id"] ?>" />
						<input type="submit" value="Delete Comment" accesskey="d" />
						<?php echo $this->FormClose(); ?>
						<?php
					}
				// }
				print("\n\t</div>\n");
				print("</div>\n");
			}
		}
		?>
        <?php
		// display comment form
		print("<div class=\"commentform\">\n");
		if ($this->HasAccess("comment"))
		{?>
		    <?php echo $this->FormOpen("addcomment"); ?>
			<label for="commentbox">Add a comment to this page:<br />
				<textarea id="commentbox" name="body" rows="6" cols="78"></textarea><br />
				<input type="submit" value="Add Comment" accesskey="s" />
            </label>
			<?php echo $this->FormClose(); ?>
			<?php
		}
		print("</div>\n");
	}
	else
	{
		?>
		<div class="commentsheader">
		<?php
			switch (count($comments))
			{
			case 0:
				print("<p>There are no comments on this page. ");
				$showcomments_text = "Add comment";
				break;
			case 1:
				print("<p>There is one comment on this page. ");
				$showcomments_text = "Display comment";
				break;
			default:
				print("<p>There are ".count($comments)." comments on this page. ");
				$showcomments_text = "Display comments";
			}
		?>
		[<a href="<?php echo $this->href("", "", "show_comments=1#comments")."\">$showcomments_text"; ?></a>]</p>
		</div>
		<?php
	}
}
?>
