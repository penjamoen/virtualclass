<?php
// Dokeos - elearning and course management software
// See license terms in dokeos/documentation/license.txt

// Training tools
// Wiki - task

// For more information: http://docs.fckeditor.net/FCKeditor_2.x/Developers_Guide/Configuration/Configuration_Options

// This is the visible toolbar set when the editor has "normal" size.
$config['ToolbarSets']['Normal'] = array(
	array('FitWindow','-','PasteWord','-','Undo','Redo'),
	array('Link','Unlink'),
	array('Image','flvPlayer','Flash','EmbedMovies','YouTube','MP3','mimetex'),
	array('Table'),
	array('Bold','Italic','Underline'),
	array('JustifyLeft','JustifyCenter','-','OrderedList','UnorderedList','-','TextColor','BGColor'),
	array('Source')
);

// This is the visible toolbar set when the editor is maximized.
// If it has not been defined, then the toolbar set for the "normal" size is used.
$config['ToolbarSets']['Maximized'] = array(
	array('FitWindow','DocProps','-','Save','NewPage','Preview','-','Templates'),
	array('Cut','Copy','Paste','PasteText','PasteWord','-','Print'),
	array('Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'),
	array('Link','Unlink','Anchor','Wikilink','Glossary'),
	'/',
	array('Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'),
	array('OrderedList','UnorderedList','-','Outdent','Indent','Blockquote','CreateDiv'),
	array('JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'),
	array('Rule','SpecialChar','PageBreak'),
	array('mimetex','asciimath','Image','imgmapPopup','Flash','MP3','EmbedMovies','flvPlayer','YouTube','googlemaps','Smiley'),
	'/',
	array('Style','FontFormat','FontName','FontSize'),
	array('TextColor','BGColor'),
	array('Table','TableInsertRowAfter','TableDeleteRows','TableInsertColumnAfter','TableDeleteColumns','TableInsertCellAfter','TableDeleteCells','TableMergeCells','TableHorizontalSplitCell','TableVerticalSplitCell','TableCellProp'),
	array('ShowBlocks','Source')
);

// Sets whether the toolbar can be collapsed/expanded or not.
// Possible values: true , false
//$config['ToolbarCanCollapse'] = true;

// Sets how the editor's toolbar should start - expanded or collapsed.
// Possible values: true , false
$config['ToolbarStartExpanded'] = false;

//This option sets the location of the toolbar.
// Possible values: 'In' , 'None' , 'Out:[TargetId]' , 'Out:[TargetWindow]([TargetId])'
//$config['ToolbarLocation'] = 'In';

// A setting for blocking copy/paste functions of the editor.
// This setting activates on leaners only. For users with other statuses there is no blocking copy/paste.
// Possible values: true , false
//$config['BlockCopyPaste'] = false;

// Here new width and height of the editor may be set.
// Possible values, examples: 300 , '250' , '100%' , ...
//$config['Width'] = '100%';
//$config['Height'] = '400';
