<?php

/*#### #### #### #### #### #### #### #### #### ####
phpLogCon - A Web Interface to Log Data.
Copyright (C) 2004-2005  Adiscon GmbH



This program is free software; you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by the
Free Software Foundation; either version 2 of the License, or (at your
option) any later version.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, 
MA  02111-1307, USA.

If you have questions about phpLogCon in general, please email info@adiscon.com. 
To learn more about phpLogCon, please visit http://www.phplogcon.com.

This Project was intiated and is maintened by Rainer Gerhards <rgerhards@hq.adiscon.com>. 
See AUTHORS to learn who helped make it become a reality.

*/#### #### #### #### #### #### #### #### #### #### 
	include 'include.php';

	WriteStandardHeader(_MSGShwEvn);
  
	//classes
	include _CLASSES . 'eventsnavigation.php';
	include _CLASSES . 'eventfilter.php';

	//the splitted sql statement
	$cmdSQLfirst_part = 'SELECT ';
	$cmdSQLmain_part = 'ID, '._DATE.', Facility, Priority, FromHost, Message, InfoUnitID FROM '._DBTABLENAME;
	$cmdSQLlast_part = ' WHERE ';

	//define the last part of the sql statment, e.g. the where part, ordery by, etc.
	$myFilter = New EventFilter;
	
	$cmdSQLlast_part .= $myFilter->GetSQLWherePart(0);
	
	$cmdSQLlast_part .= $myFilter->GetSQLSort();

	//Set Priority Filter if activated
	/*if ($Priority!=0) {
	  $cmdSQLlast_part .= " where Priority = ".$Priority;
	}
	*/
	//amount of data records displayed
	
	if($_SESSION['epp'] < 1 || $_SESSION['epp'] > 2000)
		$myEventsNavigation = new EventsNavigation(20);
	else
		$myEventsNavigation = new EventsNavigation($_SESSION['epp']);

	$myEventsNavigation->SetEventCount($global_Con, $cmdSQLlast_part);
	$num = $myEventsNavigation->GetEventCount();

	include "quick-filter.php";

/*
	echo "<form method='POST' action=''>";
	echo _MSGSrcExp . ": <input type='text' name='search' size='30'>\t";
	echo "<input type='submit' value='" . _MSGSrc . "'>";
	echo "\t<font color=red>Temporally UNAVAIBLE!!</font>";
	echo "</form>";
*/
	echo '<br><table>';

	//SQL statement to get result with limitation
	$res = db_exec_limit($global_Con, $cmdSQLfirst_part, $cmdSQLmain_part, $cmdSQLlast_part, $myEventsNavigation->GetLimitLower(), $myEventsNavigation->GetPageSize(), $myFilter->OrderBy);
 
	if($num == 0)
	{
	  //output if no data exit for the search string
	  echo '<br><b>', _MSGNoData, '</b>';
	}
	else
	{
		echo '<tr><td align="left">';
		echo _MSGEvn, ' ', $myEventsNavigation->GetLimitLower(), ' ', _MSGTo, ' ', $myEventsNavigation->GetLimitUpper(), ' ', _MSGFrm, ' ', $myEventsNavigation->GetEventCount(); 
		echo '</td><td align="right">';

		$myEventsNavigation->ShowNavigation();

?>

</td>
</tr>
</table>

<table border="0" cellspacing="0" cellpadding="0" CLASS="EventTable">
	<tr CLASS=TDHEADER>
		<td><?php echo _MSGDate; ?></td>
		<td><?php echo _MSGFac; ?></td>
		<td><?php echo _MSGPri; ?></td>
		<td><?php echo _MSGInfUI; ?></td>
		<td><?php echo _MSGHost; ?></td>
		<td><?php echo _MSGMsg; ?></td>
	</tr>

<?php

		//Read out words from phplogcon.ini which shouldn't
		//be displayed and replaced by '*'
		$file = file('phplogcon.ini');
		if($file != FALSE)
		{
			$numarrayfile = count($file);
			for($i = 0; $i < $numarrayfile; $i++)
			{
				$file[$i] = trim($file[$i]);
				if($file[$i] != '#')
				{
					if($file[$i] == '[phplogcon]')
					{
						for($j = $i+1; $j < $numarrayfile; $j++)
						{
							if( stristr($file[$j], 'wordsdontshow=') != FALSE )
							{
								$words = explode("=", $file[$j]);
								$words = explode(",", $words[1]);
							}
						}
					}
				}
			}
			$numarraywords = count($words);
		}

		$tc = 1;
		while($row = db_fetch_array($res))
		{
			if (db_errno() != 0)
			{
				echo db_errno() . ': ' . db_error(). '\n';
			}
		
			//choose InfoUnitdType  1 = SL => Syslog ; 3 = ER => Eventreporter ; O = O => Other
			switch ($row['InfoUnitID'])
			{
			  case 1:
				$infounit = 'SL';
				break;
			  case 3:
				$infounit = 'ER';
				break;
			  default:
				$infounit = 'O';
			}

			if($row['Message'] == "")
				$message = _MSGNoMsg;
			else
				$message = $row['Message'];

			// If date is today, only show the time --->
			$current_date = $row[_DATE];
			$now = date("Y-m-d 00:00:00");
			if ($current_date > $now)
			{
				$current_date = substr($current_date, 10, 16);
			}
			// <---
			


			echo '<tr>';			
			echo '<td CLASS=TD' . $tc . '><nobr>'.$current_date.'</nobr></td>'; //date
			echo '<td CLASS=TD' . $tc . '>'.$row['Facility'].'</td>'; //facility
			
			// get the description of priority (and get the the right color, if enabled)
			$pricol = 'TD' . $tc;
			$priword = FormatPriority($row['Priority'], $pricol);
			echo '<td CLASS=', $pricol, '>', $priword, '</td>'; 

			echo '<td CLASS=TD' . $tc . '>'.$infounit.'</td>'; //InfoUnit
			echo '<td CLASS=TD' . $tc . '>'.$row['FromHost'].'</td>'; //host
			
			$message = htmlspecialchars($message);
			
			if(isset($_SESSION['regexp']) && $_SESSION['regexp'] != '')
			{
				$_SESSION['regexp'] = trim($_SESSION['regexp']);
				$messageUp = strtoupper($message);
				$regexpUp = strtoupper($_SESSION['regexp']);
				$search_pos = strpos($messageUp, $regexpUp);
				if($search_pos !== FALSE)
				{
					$regexpLng = strlen($_SESSION['regexp']);
					$strCount = substr_count($messageUp, $regexpUp);
					$strTmp = $message;

					$message = "";
					for($i = 0; $i < $strCount; $i++)
					{
						$messageUp = strtoupper($strTmp);
						$search_pos = strpos($messageUp, $regexpUp);
						$subStrSt = substr($strTmp, 0 , $search_pos);
						$subStrExp = substr($strTmp, $search_pos, $regexpLng);
						$subStrEnd = substr($strTmp, ($search_pos + $regexpLng));
						$message .= $subStrSt . '<font color="' . $_SESSION['color'] . '">' . $subStrExp . '</font>';
						if($i == ($strCount - 1))
							$message .= $subStrEnd;

						$strTmp = $subStrEnd;
					}
				}
			}

			//Replace the words that had been read out from the ini file
			if($file != FALSE)
			{
				for($i = 0; $i < $numarraywords; $i++)
				{
					$repstr = '';
					$words[$i] = trim($words[$i]);
					for($j = 0; $j < strlen($words[$i]); $j++) $repstr .= '*';
					if($words[$i] != '')
						$message = eregi_replace($words[$i], $repstr, $message);
				}
			}

			echo '<td CLASS=TD', $tc, '><a CLASS="Msg" href="details.php?lid=', $row['ID'] , '">', $message, '</a></td>'; //message

			//for changing colors
			if($tc == 1) $tc = 2;
			else $tc = 1;
			/*
			echo "<td>".$row['Priority']."</td>";
			*/
			echo '</tr>', "\r\n";
		}
		echo '</table>';
	}
	WriteFooter();
?>