<?php /*dotmg modifications : contact m.randimbisoa@dotmg.net*/ ?>
<?php

// This may look a bit strange, but all possible formatting tags have to be in a single regular expression for this to work correctly. Yup!

//#dotmg [many lines] : Unclosed tags fix! For more info, m.randimbisoa@dotmg.net
if (!function_exists("wakka2callback"))
{
	function wakka2callback($things)
	{
		$thing = $things[1];
		$result='';

		static $oldIndentLevel = 0;
		static $oldIndentLength= 0;
		static $indentClosers = array();
		static $newIndentSpace= array();
		static $br = 1;
  		static $trigger_bold = 0;
		static $trigger_italic = 0;
  		static $trigger_underline = 0;
  		static $trigger_monospace = 0;
  		static $trigger_notes = 0;
  		static $trigger_strike = 0;
  		static $trigger_inserted = 0;
  		static $trigger_floatl = 0;
  		static $trigger_keys = 0;
  		static $trigger_strike = 0;
  		static $trigger_inserted = 0;
  		static $trigger_center = 0;
  		static $trigger_l = array(-1, 0, 0, 0, 0, 0);

		global $wakka;

		if ((!is_array($things)) && ($things == 'closetags'))
		{
  			if ($trigger_strike % 2) echo ('</span>');
			if ($trigger_notes % 2) echo ('</span>');
			if ($trigger_inserted % 2) echo ('</span>');
			if ($trigger_underline % 2) echo('</span>');
  			if ($trigger_floatl % 2) echo ('</div>');
			if ($trigger_center % 2) echo ('</div>');
  			if ($trigger_italic % 2) echo('</em>');
  			if ($trigger_monospace % 2) echo('</tt>');
  			if ($trigger_bold % 2) echo('</strong>');
  			for ($i = 1; $i<=5; $i ++)
  				if ($trigger_l[$i] % 2) echo ("</h$i>");
			$trigger_bold = $trigger_center = $trigger_floatl = $trigger_inserted = $trigger_italic =$trigger_keys = 0;
			$trigger_l = array(-1, 0, 0, 0, 0, 0);
			$trigger_monospace = $trigger_notes = $trigger_strike = $trigger_underline = 0;
			return;
		}
		// convert HTML thingies
		if ($thing == "<")
			return "&lt;";
		else if ($thing == ">")
			return "&gt;";
		// float box left
		else if ($thing == "<<")
		{
			return (++$trigger_floatl % 2 ? "<div class=\"floatl\">\n" : "\n</div>\n");
		}
		// float box right
		else if ($thing == ">>")
		{
			return (++$trigger_floatl % 2 ? "<div class=\"floatr\">\n" : "\n</div>\n");
		}
		// clear floated box
		else if ($thing == "::c::")
		{
			return ("<div class=\"clear\">&nbsp;</div>\n");
		}
		// keyboard
		else if ($thing == "#%")
		{
			return (++$trigger_keys % 2 ? "<kbd class=\"keys\">" : "</kbd>");
		}
		// bold
		else if ($thing == "**")
		{
			return (++$trigger_bold % 2 ? "<strong>" : "</strong>");
		}
		// italic
		else if ($thing == "//")
		{
			return (++$trigger_italic % 2 ? "<em>" : "</em>");
		}
		// underlinue
		else if ($thing == "__")
		{
			return (++$trigger_underline % 2 ? "<span class=\"underline\">" : "</span>");
		}
		// monospace
		else if ($thing == "##")
		{
			return (++$trigger_monospace % 2 ? "<tt>" : "</tt>");
		}
		// notes
		else if ($thing == "''")
		{
			return (++$trigger_notes % 2 ? "<span class=\"notes\">" : "</span>");
		}
		// strikethrough
		else if ($thing == "++")
		{
			return (++$trigger_strike % 2 ? "<span class=\"deletions\">" : "</span>");
		} 
		// Inserted
		else if ($thing == "&pound;&pound;")
		{
			return (++$trigger_inserted % 2 ? "<span class=\"additions\">" : "</span>");
		}
		// centre
		else if ($thing == "@@")
		{
			return (++$trigger_center % 2 ? "<div class=\"centre\">\n" : "\n</div>\n");
		}         
		// urls
		else if (preg_match("/^([a-z]+:\/\/\S+?)([^[:alnum:]^\/])?$/", $thing, $matches)) {
			$url = $matches[1];
			if (preg_match("/^(.*)\.(gif|jpg|png)/si", $url)) return "<img src=\"$url\" alt=\"image\" />".$matches[2]; 
			return $wakka->Link($url).$matches[2]; 
		}
		// header level 5
		else if ($thing == "==")
		{
				$br = 0;
				return (++$trigger_l[5] % 2 ? "<h5>" : "</h5>\n");
		}
		// header level 4
		else if ($thing == "===")
		{
				$br = 0;
				return (++$trigger_l[4] % 2 ? "<h4>" : "</h4>\n");
		}
		// header level 3
		else if ($thing == "====")
		{
				$br = 0;
				return (++$trigger_l[3] % 2 ? "<h3>" : "</h3>\n");
		}
		// header level 2
		else if ($thing == "=====")
		{
				$br = 0;
				return (++$trigger_l[2] % 2 ? "<h2>" : "</h2>\n");
		}
		// header level 1
		else if ($thing == "======")
		{
				$br = 0;
				return (++$trigger_l[1] % 2 ? "<h1>" : "</h1>\n");
		}
		// forced line breaks
		else if ($thing == "---")
		{
			return "<br />";
		}
		// escaped text
		else if (preg_match("/^\"\"(.*)\"\"$/s", $thing, $matches))
		{
			$allowed_double_doublequote_html = $wakka->GetConfigValue("double_doublequote_html");
			if ($allowed_double_doublequote_html == 'safe')
			{
				$filtered_output = $wakka->ReturnSafeHTML($matches[1]);
				return $filtered_output;
			}
			elseif ($allowed_double_doublequote_html == 'raw')
			{
				return $matches[1];
			} 
			else 
			{
				return htmlspecialchars($matches[1]);
			}
		}
		// code text
		else if (preg_match("/^\%\%(.*)\%\%$/s", $thing, $matches))
		{
			// check if a language has been specified
			$code = $matches[1];
			$language = "";
			if (preg_match("/^\((.+?)\)(.*)$/s", $code, $matches))
			{
				list(, $language, $code) = $matches;
			}
			switch ($language)
			{
			case "php":
				$formatter = "php";
				break;
			case "ini":
				$formatter = "ini";
				break;
			case "email":
				$formatter = "email";
				break;
			default:
				$formatter = "code";
			}

			$output = "<div class=\"code\">\n";
			$output .= $wakka->Format(trim($code), $formatter);
			$output .= "</div>\n";

			return $output;
		}
		// forced links
		// \S : any character that is not a whitespace character
		// \s : any whitespace character
		else if (preg_match("/^\[\[(\S*)(\s+(.+))?\]\]$/", $thing, $matches))
		{
			list (, $url, , $text) = $matches;
			if ($url)
			{
				//if ($url!=($url=(preg_replace("/@@|&pound;&pound;||\[\[/","",$url))))$result="</span>";
				if (!$text) $text = $url;
				//$text=preg_replace("/@@|&pound;&pound;|\[\[/","",$text);
				return $result.$wakka->Link($url, "", $text);
			}
			else
			{
				return "";
			}
		}
		// indented text
 		elseif (preg_match("/\n([\t,~]+)(-|([0-9,a-z,A-Z,���,����]+)\))?(\n|$)/s", $thing, $matches))
		{
			// new line
			$result .= ($br ? "<br />\n" : "\n");
			
			// we definitely want no line break in this one.
			$br = 0;

			// find out which indent type we want
			$newIndentType = $matches[2];
         if (!$newIndentType) { $opener = "<div class=\"indent\">"; $closer = "</div>"; $br = 1; }
         elseif ($newIndentType == "-") { $opener = "<ul><li>"; $closer = "</li></ul>"; $li = 1; }
         else { $opener = "<ol type=\"". substr($newIndentType, 0, 1)."\"><li>"; $closer = "</li></ol>"; $li = 1; }

			// get new indent level
			$newIndentLevel = strlen($matches[1]);
			if ($newIndentLevel > $oldIndentLevel) 
			{
				for ($i = 0; $i < $newIndentLevel - $oldIndentLevel; $i++)
				{
					$result .= $opener;
					array_push($indentClosers, $closer);
				}
			}
			else if ($newIndentLevel < $oldIndentLevel)
			{
				for ($i = 0; $i < $oldIndentLevel - $newIndentLevel; $i++)
				{
					$result .= array_pop($indentClosers);
				}
			}
			
			$oldIndentLevel = $newIndentLevel;

			if (isset($li) && !preg_match("/".str_replace(")", "\)", $opener)."$/", $result))
			{
				$result .= "</li><li>";
			}

			return $result;
		}
		// new lines
		else if ($thing == "\n")
		{
			// if we got here, there was no tab in the next line; this means that we can close all open indents.
			$c = count($indentClosers);
			for ($i = 0; $i < $c; $i++)
			{
				$result .= array_pop($indentClosers);
				$br = 0;
			}
			$oldIndentLevel = 0;
			$oldIndentLength= 0;
			$newIndentSpace=array();

			$result .= ($br ? "<br />\n" : "\n");
			$br = 1;
			return $result;
		}
		// Actions
		else if (preg_match("/^\{\{(.*?)\}\}$/s", $thing, $matches))
		{
			if ($matches[1])
				return $wakka->Action($matches[1]);
			else
				return "{{}}";
		}
		// interwiki links!
				else if (preg_match("/^[A-Z,���][A-Z,a-z,���,����]+[:]([A-Z,a-z,0-9,���,����]*)$/s", $thing))

		{
			return $wakka->Link($thing);
		}
		// wiki links!
		else if (preg_match("/^[A-Z,���][a-z,����]+[A-Z,0-9,���][A-Z,a-z,0-9,���,����]*$/s", $thing))
		{
			return $wakka->Link($thing);
		}
		// separators
		else if (preg_match("/-{4,}/", $thing, $matches))
		{
			// TODO: This could probably be improved for situations where someone puts text on the same line as a separator.
			//       Which is a stupid thing to do anyway! HAW HAW! Ahem.
			$br = 0;
			return "<hr />\n";
		}
		// if we reach this point, it must have been an accident.
		return $thing;
	}
}


$text = str_replace("\r", "", $text);
$text = chop($text)."\n";

$text = preg_replace_callback(
	"/(\%\%.*?\%\%|".
	"\"\".*?\"\"|".
	"\[\[[^\[].*?\]\]|". 
	"-{4,}|---|".
	"\b[a-z]+:\/\/\S+|".
	"\*\*|\'\'|\#\#|\#\%|@@|::c::|\>\>|\<\<|&pound;&pound;|\+\+|__|<|>|\/\/|".
	"======|=====|====|===|==|".
	"\n([\t,~]+)(-|[0-9,a-z,A-Z]+\))?|".
	"\{\{.*?\}\}|".
	"\b[A-Z,���][A-Z,a-z,���,����]+[:]([A-Z,a-z,0-9,���,����]*)\b|".
	"\b([A-Z,���][a-z,����]+[A-Z,0-9,���][A-Z,a-z,0-9,���,����]*)\b|".
	"\n)/ms", "wakka2callback", $text);
/*$pattern2 = "/^(\040|\t)*(?!<|\040)(.+)$/m"; //matches any line with no <element> (and variable leading space) - assume a paragraph
$replace2 = "<p>\\2</p>";
$text=preg_replace($pattern2,$replace2,$text);

$pattern1 = "/^(\040|\t)*(<(?!hr|(\/|)h[1-6]|br|(\/|)li|(\/|)[uo]l|(\/|)div|(\/|)p).*)$/m"; //matches any <element>text lines not considered block formatting
$replace1 = "<p>\\2</p>";
$text=preg_replace($pattern1,$replace1,$text);*/

$pattern3 = "/(\n{2,})/m"; //strips multiple newlines
$replace3 = "\n";
$text=preg_replace($pattern3,$replace3,$text);

// we're cutting the last <br />
$text = preg_replace("/<br \/>$/","", trim($text));
echo ($text);
wakka2callback('closetags');
?>
