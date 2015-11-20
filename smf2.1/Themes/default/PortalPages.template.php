<?php
// Version: 1.1; PortalPages

function template_view_page()
{
	template_view_page_default();
}

function template_view_page_default()
{
	global $context;

	if (empty($context['SPortal']['page']['style']['no_title']))
	{
		echo '
			<div class="', in_array($context['SPortal']['page']['style']['title']['class'], array('titlebg', 'titlebg2')) ? 'title_bar' : 'cat_bar', '"', !empty($context['SPortal']['page']['style']['title']['style']) ? ' style="' . $context['SPortal']['page']['style']['title']['style'] . '"' : '', '>
				<h3 class="', $context['SPortal']['page']['style']['title']['class'], '" style="">
					', $context['SPortal']['page']['title'], '
				</h3>
			</div>';
	}

	if (strpos($context['SPortal']['page']['style']['body']['class'], 'roundframe') !== false)
	{
		echo '
				<span class="upperframe"><span></span></span>';
	}

	echo '
				<div class="sp_page_title">
					<div class="', $context['SPortal']['page']['style']['body']['class'], '">';

	if (empty($context['SPortal']['page']['style']['no_body']))
	{
		echo '
						<span class="topslice"><span></span></span>';
	}

	echo '
						<div class="sp_content_padding"', !empty($context['SPortal']['page']['style']['body']['style']) ? ' style="' . $context['SPortal']['page']['style']['body']['style'] . '"' : '', '>';

	sportal_parse_page($context['SPortal']['page']['body'], $context['SPortal']['page']['type']);

	echo '
						</div>';

	if (empty($context['SPortal']['page']['style']['no_body']))
	{
		echo '
						<span class="botslice"><span></span></span>';
	}

	echo '
					</div>
				</div>';

	if (strpos($context['SPortal']['page']['style']['body']['class'], 'roundframe') !== false)
	{
		echo '
				<span class="lowerframe"><span></span></span>';
	}

	if (!empty($context['SPortal']['page']['resize']))
		echo '<script type="text/javascript">window.onload = sp_resizeContentSection();</script>';
}

?>