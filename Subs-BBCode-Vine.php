<?php
/**********************************************************************************
* Subs-BBCode-Vine.php
***********************************************************************************
* This mod is licensed under the 2-clause BSD License, which can be found here:
*	http://opensource.org/licenses/BSD-2-Clause
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/
if (!defined('SMF')) 
	die('Hacking attempt...');

function BBCode_Vine(&$bbc)
{
	// Format: [vine width=x height=x frameborder=x]{Vine ID}[/vine]
	$bbc[] = array(
		'tag' => 'vine',
		'type' => 'unparsed_content',
		'parameters' => array(
			'width' => array('match' => '(\d+)'),
			'height' => array('optional' => true, 'match' => '(\d+)'),
			'frameborder' => array('optional' => true, 'match' => '(\d+)'),
		),
		'validate' => 'BBCode_Vine_Validate',
		'content' => '{width}|{height}|{frameborder}',
		'disabled_content' => '$1',
	);

	// Format: [vine width=x height=x frameborder=x]{Vine ID}[/vine]
	$bbc[] = array(
		'tag' => 'vine',
		'type' => 'unparsed_content',
		'parameters' => array(
			'frameborder' => array('match' => '(\d+)'),
		),
		'validate' => 'BBCode_Vine_Validate',
		'content' => '0|0|{frameborder}',
		'disabled_content' => '$1',
	);

	// Format: [vine]{Vine ID}[/vine]
	$bbc[] = array(
		'tag' => 'vine',
		'type' => 'unparsed_content',
		'validate' => 'BBCode_Vine_Validate',
		'content' => '0|0|0',
		'disabled_content' => '$1',
	);
}

function BBCode_Vine_Button(&$buttons)
{
	$buttons[count($buttons) - 1][] = array(
		'image' => 'vine',
		'code' => 'vine',
		'description' => 'vine',
		'before' => '[vine]',
		'after' => '[/vine]',
	);
}

function BBCode_Vine_Validate(&$tag, &$data, &$disabled)
{
	global $txt, $modSettings;
	
	if (empty($data))
		return ($tag['content'] = $txt['vine_no_post_id']);
	$data = strtr(trim($data), array('<br />' => ''));
	if (strlen($data) !== 11)
	{
		if (strpos($data, 'http://') !== 0 && strpos($data, 'https://') !== 0)
			$data = 'http://' . $data;
		$pattern = '#(http|https)://(|(.+?).)vine.co/v/((.+?){11})(|(\?|/)(.+?))#i';
		if (!preg_match($pattern, $data, $parts))
			return ($tag['content'] = $txt['vine_no_post_id']);
		$data = $parts[4];
	}
	list($width, $height, $frameborder) = explode('|', $tag['content']);
	if (empty($width) && !empty($modSettings['Vine_default_width']))
		$width = $modSettings['Vine_default_width'];
	if (empty($height) && !empty($modSettings['Vine_default_height']))
		$height = $modSettings['Vine_default_height'];
	$tag['content'] = '<div style="' . (empty($width) ? '' : 'max-width: ' . $width . 'px;') . (empty($height) ? '' : 'max-height: ' . $height . 'px;') . '"><div class="vine-wrapper">' .
		'<iframe src="https://vine.co/v/' . $data .'/card" scrolling="no" frameborder="' . $frameborder . '"></iframe></div></div>';
}

function BBCode_Vine_LoadTheme()
{
	global $context, $settings;
	$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/BBCode-Vine.css" />';
	$context['allowed_html_tags'][] = '<iframe>';
}

function BBCode_Vine_Settings(&$config_vars)
{
	$config_vars[] = array('int', 'Vine_default_width');
	$config_vars[] = array('int', 'Vine_default_height');
}

function BBCode_Vine_Embed(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	$pattern = '~(?<=[\s>\.(;\'"]|^)(?:https?\:\/\/)?(?:www\.)?vine.co\/v\/([\w\-_%]){11}+\??[/\w\-_\~%@\?;=#}\\\\]?~';
	$message = preg_replace($pattern, '[vine]$0[/vine]', $message);
}

?>