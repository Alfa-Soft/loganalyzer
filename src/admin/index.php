<?php
/*
	*********************************************************************
	* phpLogCon - http://www.phplogcon.org
	* -----------------------------------------------------------------
	* Admin Index File											
	*																	
	* -> Shows ...
	*																	
	* All directives are explained within this file
	*
	* Copyright (C) 2008 Adiscon GmbH.
	*
	* This file is part of phpLogCon.
	*
	* PhpLogCon is free software: you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published by
	* the Free Software Foundation, either version 3 of the License, or
	* (at your option) any later version.
	*
	* PhpLogCon is distributed in the hope that it will be useful,
	* but WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	* GNU General Public License for more details.
	*
	* You should have received a copy of the GNU General Public License
	* along with phpLogCon. If not, see <http://www.gnu.org/licenses/>.
	*
	* A copy of the GPL can be found in the file "COPYING" in this
	* distribution				
	*********************************************************************
*/

// *** Default includes	and procedures *** //
define('IN_PHPLOGCON', true);
$gl_root_path = './../';

// Now include necessary include files!
include($gl_root_path . 'include/functions_common.php');
include($gl_root_path . 'include/functions_frontendhelpers.php');
include($gl_root_path . 'include/functions_filters.php');

// Include LogStream facility
// include($gl_root_path . 'classes/logstream.class.php');

// Set PAGE to be ADMINPAGE!
define('IS_ADMINPAGE', true);
$content['IS_ADMINPAGE'] = true;

InitPhpLogCon();
InitSourceConfigs();
InitFrontEndDefaults();	// Only in WebFrontEnd
InitFilterHelpers();	// Helpers for frontend filtering!

// Init admin langauge file now!
IncludeLanguageFile( $gl_root_path . '/lang/' . $LANG . '/admin.php' );

// --- BEGIN Custom Code
if ( isset($_SESSION['SESSION_ISADMIN']) && $_SESSION['SESSION_ISADMIN'] == 1 ) 
{
	$content['EditAllowed'] = true;
	$content['DISABLE_GLOBALEDIT_FORMCONTROL'] = "";
}
else	
{
	$content['EditAllowed'] = false;
	$content['DISABLE_GLOBALEDIT_FORMCONTROL'] = "disabled";
}

// --- First thing to do is to check the op get parameter!
// Check for changes first | Abort if Edit is not allowed
if ( isset($_GET['op']) && isset($_GET['value']) )
{
	if ( $_GET['op'] == "enableuserops" )
	{
		$iNewVal = intval($_GET['value']);
		if ( $iNewVal == 1 )
			$USERCFG['UserOverwriteOptions'] = 1;
		else
			$USERCFG['UserOverwriteOptions'] = 0;

		// Enable User Options!
		WriteConfigValue( "UserOverwriteOptions", false, $content['SESSION_USERID'] );
	}
}
// ---

// --- Check if user wants to overwrite
$UserOverwriteOptions = GetConfigSetting("UserOverwriteOptions", 0, CFGLEVEL_USER);
if ( $UserOverwriteOptions == 1 )
{
	$content['ENABLEUSEROPTIONS'] = true;
}
else
{
	$content['ENABLEUSEROPTIONS'] = false;


}
// ---

// Check for changes first | Abort if Edit is not allowed
if ( isset($_POST['op']) )
{
	if ( $_POST['op'] == "edit" )
	{
		// Do if User is ADMIN
		if ( $content['EditAllowed'] )
		{
			// Language needs special treatment
			if ( isset ($_POST['ViewDefaultLanguage']) )
			{ 
				$tmpvar = DB_RemoveBadChars($_POST['ViewDefaultLanguage']); 
				if ( VerifyLanguage($tmpvar) )
					$content['ViewDefaultLanguage'] = $tmpvar;
			}

			// Read default theme
			if ( isset ($_POST['ViewDefaultTheme']) ) { $content['ViewDefaultTheme'] = $_POST['ViewDefaultTheme']; }

			// Read default VIEW | Check if View exists as well!
			if ( isset ($_POST['DefaultViewsID']) && isset($content['Views'][$_POST['DefaultViewsID']] )) { $content['DefaultViewsID'] = $_POST['DefaultViewsID']; }

			// Read default SOURCES | Check if Source exists as well!
			if ( isset ($_POST['DefaultSourceID']) && isset($content['Sources'][$_POST['DefaultSourceID']] )) { $content['DefaultSourceID'] = $_POST['DefaultSourceID']; }

			// Read checkboxes
			if ( isset ($_POST['ViewUseTodayYesterday']) ) { $content['ViewUseTodayYesterday'] = 1; } else { $content['ViewUseTodayYesterday'] = 0; } 
			if ( isset ($_POST['ViewEnableDetailPopups']) ) { $content['ViewEnableDetailPopups'] = 1; } else { $content['ViewEnableDetailPopups'] = 0; } 
			if ( isset ($_POST['EnableIPAddressResolve']) ) { $content['EnableIPAddressResolve'] = 1; } else { $content['EnableIPAddressResolve'] = 0; } 
			if ( isset ($_POST['MiscShowDebugMsg']) ) { $content['MiscShowDebugMsg'] = 1; } else { $content['MiscShowDebugMsg'] = 0; } 
			if ( isset ($_POST['MiscShowDebugGridCounter']) ) { $content['MiscShowDebugGridCounter'] = 1; } else { $content['MiscShowDebugGridCounter'] = 0; } 
			if ( isset ($_POST['MiscShowPageRenderStats']) ) { $content['MiscShowPageRenderStats'] = 1; } else { $content['MiscShowPageRenderStats'] = 0; } 
			if ( isset ($_POST['MiscEnableGzipCompression']) ) { $content['MiscEnableGzipCompression'] = 1; } else { $content['MiscEnableGzipCompression'] = 0; } 
			if ( isset ($_POST['DebugUserLogin']) ) { $content['DebugUserLogin'] = 1; } else { $content['DebugUserLogin'] = 0; } 
			if ( isset ($_POST['SuppressDuplicatedMessages']) ) { $content['SuppressDuplicatedMessages'] = 1; } else { $content['SuppressDuplicatedMessages'] = 0; } 

			// Read Text number fields
			if ( isset ($_POST['ViewMessageCharacterLimit']) && is_numeric($_POST['ViewMessageCharacterLimit']) ) { $content['ViewMessageCharacterLimit'] = $_POST['ViewMessageCharacterLimit']; }
			if ( isset ($_POST['ViewEntriesPerPage']) && is_numeric($_POST['ViewEntriesPerPage']) ) { $content['ViewEntriesPerPage'] = $_POST['ViewEntriesPerPage']; }
			if ( isset ($_POST['ViewEnableAutoReloadSeconds']) && is_numeric($_POST['ViewEnableAutoReloadSeconds']) ) { $content['ViewEnableAutoReloadSeconds'] = $_POST['ViewEnableAutoReloadSeconds']; }

			// Read Text fields
			if ( isset ($_POST['PrependTitle']) ) { $content['PrependTitle'] = $_POST['PrependTitle']; }
			if ( isset ($_POST['SearchCustomButtonCaption']) ) { $content['SearchCustomButtonCaption'] = $_POST['SearchCustomButtonCaption']; }
			if ( isset ($_POST['SearchCustomButtonSearch']) ) { $content['SearchCustomButtonSearch'] = $_POST['SearchCustomButtonSearch']; }

			// Save configuration variables now
			SaveGeneralSettingsIntoDB();
		}
		
		// Do if User wants extra options
		if ( $content['ENABLEUSEROPTIONS'] )
		{
			// Language needs special treatment
			if ( isset ($_POST['User_ViewDefaultLanguage']) )
			{ 
				$tmpvar = DB_RemoveBadChars($_POST['User_ViewDefaultLanguage']); 
				if ( VerifyLanguage($tmpvar) )
					$USERCFG['ViewDefaultLanguage'] = $tmpvar;
			}

			// Read default theme
			if ( isset ($_POST['User_ViewDefaultTheme']) ) { $USERCFG['ViewDefaultTheme'] = $_POST['User_ViewDefaultTheme']; }

			// Read default VIEW | Check if View exists as well!
			if ( isset ($_POST['User_DefaultViewsID']) && isset($content['Views'][$_POST['User_DefaultViewsID']] )) { $USERCFG['DefaultViewsID'] = $_POST['User_DefaultViewsID']; }

			// Read default SOURCES | Check if Source exists as well!
			if ( isset ($_POST['User_DefaultSourceID']) && isset($content['Sources'][$_POST['User_DefaultSourceID']] )) { $USERCFG['DefaultSourceID'] = $_POST['User_DefaultSourceID']; }

			// Read checkboxes
			if ( isset ($_POST['User_ViewUseTodayYesterday']) ) { $USERCFG['ViewUseTodayYesterday'] = 1; } else { $USERCFG['ViewUseTodayYesterday'] = 0; } 
			if ( isset ($_POST['User_ViewEnableDetailPopups']) ) { $USERCFG['ViewEnableDetailPopups'] = 1; } else { $USERCFG['ViewEnableDetailPopups'] = 0; } 
			if ( isset ($_POST['User_EnableIPAddressResolve']) ) { $USERCFG['EnableIPAddressResolve'] = 1; } else { $USERCFG['EnableIPAddressResolve'] = 0; } 
			if ( isset ($_POST['User_MiscShowDebugMsg']) ) { $USERCFG['MiscShowDebugMsg'] = 1; } else { $USERCFG['MiscShowDebugMsg'] = 0; } 
			if ( isset ($_POST['User_MiscShowDebugGridCounter']) ) { $USERCFG['MiscShowDebugGridCounter'] = 1; } else { $USERCFG['MiscShowDebugGridCounter'] = 0; } 
			if ( isset ($_POST['User_MiscShowPageRenderStats']) ) { $USERCFG['MiscShowPageRenderStats'] = 1; } else { $USERCFG['MiscShowPageRenderStats'] = 0; } 
			if ( isset ($_POST['User_MiscEnableGzipCompression']) ) { $USERCFG['MiscEnableGzipCompression'] = 1; } else { $USERCFG['MiscEnableGzipCompression'] = 0; } 
// DISABLED FOR USER!			if ( isset ($_POST['User_DebugUserLogin']) ) { $USERCFG['DebugUserLogin'] = 1; } else { $USERCFG['DebugUserLogin'] = 0; } 
			if ( isset ($_POST['User_SuppressDuplicatedMessages']) ) { $USERCFG['SuppressDuplicatedMessages'] = 1; } else { $USERCFG['SuppressDuplicatedMessages'] = 0; } 

			// Read Text number fields
			if ( isset ($_POST['User_ViewMessageCharacterLimit']) && is_numeric($_POST['User_ViewMessageCharacterLimit']) ) { $USERCFG['ViewMessageCharacterLimit'] = $_POST['User_ViewMessageCharacterLimit']; }
			if ( isset ($_POST['User_ViewEntriesPerPage']) && is_numeric($_POST['User_ViewEntriesPerPage']) ) { $USERCFG['ViewEntriesPerPage'] = $_POST['User_ViewEntriesPerPage']; }
			if ( isset ($_POST['User_ViewEnableAutoReloadSeconds']) && is_numeric($_POST['User_ViewEnableAutoReloadSeconds']) ) { $USERCFG['ViewEnableAutoReloadSeconds'] = $_POST['User_ViewEnableAutoReloadSeconds']; }

			// Read Text fields
			if ( isset ($_POST['User_PrependTitle']) ) { $USERCFG['PrependTitle'] = $_POST['User_PrependTitle']; }
			if ( isset ($_POST['User_SearchCustomButtonCaption']) ) { $USERCFG['SearchCustomButtonCaption'] = $_POST['User_SearchCustomButtonCaption']; }
			if ( isset ($_POST['User_SearchCustomButtonSearch']) ) { $USERCFG['SearchCustomButtonSearch'] = $_POST['User_SearchCustomButtonSearch']; }

			// Save configuration variables now
			SaveUserGeneralSettingsIntoDB();
		}

		// Do a redirect
		RedirectResult( $content['LN_GEN_SUCCESSFULLYSAVED'], "index.php" );
	}
}


// Set checkbox States
if ($content['ViewUseTodayYesterday'] == 1) { $content['ViewUseTodayYesterday_checked'] = "checked"; } else { $content['ViewUseTodayYesterday_checked'] = ""; }
if ($content['ViewEnableDetailPopups'] == 1) { $content['ViewEnableDetailPopups_checked'] = "checked"; } else { $content['ViewEnableDetailPopups_checked'] = ""; }
if ($content['EnableIPAddressResolve'] == 1) { $content['EnableIPAddressResolve_checked'] = "checked"; } else { $content['EnableIPAddressResolve_checked'] = ""; }

if ($content['MiscShowDebugMsg'] == 1) { $content['MiscShowDebugMsg_checked'] = "checked"; } else { $content['MiscShowDebugMsg_checked'] = ""; }
if ($content['MiscShowDebugGridCounter'] == 1) { $content['MiscShowDebugGridCounter_checked'] = "checked"; } else { $content['MiscShowDebugGridCounter_checked'] = ""; }
if ($content['MiscShowPageRenderStats'] == 1) { $content['MiscShowPageRenderStats_checked'] = "checked"; } else { $content['MiscShowPageRenderStats_checked'] = ""; }
if ($content['MiscEnableGzipCompression'] == 1) { $content['MiscEnableGzipCompression_checked'] = "checked"; } else { $content['MiscEnableGzipCompression_checked'] = ""; }
if ($content['DebugUserLogin'] == 1) { $content['DebugUserLogin_checked'] = "checked"; } else { $content['DebugUserLogin_checked'] = ""; }
if ($content['SuppressDuplicatedMessages'] == 1) { $content['SuppressDuplicatedMessages_checked'] = "checked"; } else { $content['SuppressDuplicatedMessages_checked'] = ""; }
// --- 

// --- Init for DefaultView field!
// copy Views Array
$content['VIEWS'] = $content['Views'];
if ( !isset($content['DefaultViewsID']) ) { $content['DefaultViewsID'] = 'SYSLOG'; }
foreach ( $content['VIEWS'] as $myView )
{
	if ( $myView['ID'] == $content['DefaultViewsID'] )
		$content['VIEWS'][ $myView['ID'] ]['selected'] = "selected";
	else
		$content['VIEWS'][ $myView['ID'] ]['selected'] = "";
}
// --- 

// --- Init for DefaultSource  field!
// copy Sources Array
$content['SOURCES'] = $content['Sources'];
if ( !isset($content['DefaultSourceID']) ) { $content['DefaultSourceID'] = ''; }
foreach ( $content['SOURCES'] as $mySource )
{
	if ( $mySource['ID'] == $content['DefaultSourceID'] )
		$content['SOURCES'][ $mySource['ID'] ]['selected'] = "selected";
	else
		$content['SOURCES'][ $mySource['ID'] ]['selected'] = "";
}
// --- 

// Do if User wants extra options
if ( $content['ENABLEUSEROPTIONS'] )
{
	// Set checkbox States
	if ( GetConfigSetting('ViewUseTodayYesterday', $content['ViewUseTodayYesterday'], CFGLEVEL_USER) == 1) { $content['User_ViewUseTodayYesterday_checked'] = "checked"; } else { $content['User_ViewUseTodayYesterday_checked'] = ""; }
	if ( GetConfigSetting('ViewEnableDetailPopups', $content['ViewEnableDetailPopups'], CFGLEVEL_USER) == 1) { $content['User_ViewEnableDetailPopups_checked'] = "checked"; } else { $content['User_ViewEnableDetailPopups_checked'] = ""; }
	if ( GetConfigSetting('EnableIPAddressResolve', $content['EnableIPAddressResolve'], CFGLEVEL_USER) == 1) { $content['User_EnableIPAddressResolve_checked'] = "checked"; } else { $content['User_EnableIPAddressResolve_checked'] = ""; }

	if ( GetConfigSetting('MiscShowDebugMsg', $content['MiscShowDebugMsg'], CFGLEVEL_USER) == 1) { $content['User_MiscShowDebugMsg_checked'] = "checked"; } else { $content['User_MiscShowDebugMsg_checked'] = ""; }
	if ( GetConfigSetting('MiscShowDebugGridCounter', $content['MiscShowDebugGridCounter'], CFGLEVEL_USER) == 1) { $content['User_MiscShowDebugGridCounter_checked'] = "checked"; } else { $content['User_MiscShowDebugGridCounter_checked'] = ""; }
	if ( GetConfigSetting('MiscShowPageRenderStats', $content['MiscShowPageRenderStats'], CFGLEVEL_USER) == 1) { $content['User_MiscShowPageRenderStats_checked'] = "checked"; } else { $content['User_MiscShowPageRenderStats_checked'] = ""; }
	if ( GetConfigSetting('MiscEnableGzipCompression', $content['MiscEnableGzipCompression'], CFGLEVEL_USER) == 1) { $content['User_MiscEnableGzipCompression_checked'] = "checked"; } else { $content['User_MiscEnableGzipCompression_checked'] = ""; }
	if ( GetConfigSetting('SuppressDuplicatedMessages', $content['SuppressDuplicatedMessages'], CFGLEVEL_USER) == 1) { $content['User_SuppressDuplicatedMessages_checked'] = "checked"; } else { $content['User_SuppressDuplicatedMessages_checked'] = ""; }
	// --- 

	// --- Set TextFields!
	$content['User_PrependTitle'] = GetConfigSetting('PrependTitle', $content['PrependTitle'], CFGLEVEL_USER);
	$content['User_ViewMessageCharacterLimit'] = GetConfigSetting('ViewMessageCharacterLimit', $content['ViewMessageCharacterLimit'], CFGLEVEL_USER);
	$content['User_ViewEntriesPerPage'] = GetConfigSetting('ViewEntriesPerPage', $content['ViewEntriesPerPage'], CFGLEVEL_USER);
	$content['User_ViewEnableAutoReloadSeconds'] = GetConfigSetting('ViewEnableAutoReloadSeconds', $content['ViewEnableAutoReloadSeconds'], CFGLEVEL_USER);
	$content['User_SearchCustomButtonCaption'] = GetConfigSetting('SearchCustomButtonCaption', $content['SearchCustomButtonCaption'], CFGLEVEL_USER);
	$content['User_SearchCustomButtonSearch'] = GetConfigSetting('SearchCustomButtonSearch', $content['SearchCustomButtonSearch'], CFGLEVEL_USER);
	// ---

	// --- Init for ViewDefaultTheme field!
	// copy STYLES Array
	$content['USER_STYLES'] = $content['STYLES'];
	$userStyleID = GetConfigSetting('ViewDefaultTheme', $content['ViewDefaultTheme'], CFGLEVEL_USER);
	foreach ( $content['USER_STYLES'] as &$myStyle )
	{
		if ( $myStyle['StyleName'] == $userStyleID )
			$myStyle['selected'] = "selected";
		else
			$myStyle['selected'] = "";
	}
	// --- 

	// --- Init for ViewDefaultLanguage field!
	// copy LANGUAGES Array
	$content['USER_LANGUAGES'] = $content['LANGUAGES'];
	$userLangID = GetConfigSetting('ViewDefaultLanguage', $content['ViewDefaultLanguage'], CFGLEVEL_USER);
	foreach ( $content['USER_LANGUAGES'] as &$myLang )
	{
		if ( $myLang['langcode'] == $userLangID )
			$myLang['selected'] = "selected";
		else
			$myLang['selected'] = "";
	}
	// --- 

	// --- Init for DefaultView field!
	// copy Views Array
	$content['USER_VIEWS'] = $content['Views'];
	$userViewID = GetConfigSetting('DefaultViewsID', $content['DefaultViewsID'], CFGLEVEL_USER);
	foreach ( $content['USER_VIEWS'] as &$myView )
	{
		if ( $myView['ID'] == $userViewID )
			$myView['selected'] = "selected";
		else
			$myView['selected'] = "";
	}
	// --- 

	// --- Init for DefaultSource field!
	// copy Sources Array
	$content['USER_SOURCES'] = $content['Sources'];
	$userSourceID = GetConfigSetting('DefaultViewsID', $content['DefaultViewsID'], CFGLEVEL_USER);
	foreach ( $content['USER_SOURCES'] as &$mySource )
	{
		if ( $mySource['ID'] == $userSourceID )
			$mySource['selected'] = "selected";
		else
			$mySource['selected'] = "";
	}
	// --- 
}

// --- BEGIN CREATE TITLE
$content['TITLE'] = InitPageTitle();
$content['TITLE'] .= " :: " . $content['LN_ADMINMENU_GENOPT'];
// --- END CREATE TITLE

// --- Parsen and Output
InitTemplateParser();
$page -> parser($content, "admin/admin_index.html");
$page -> output(); 
// --- 


?>