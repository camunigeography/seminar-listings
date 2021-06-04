

<div class="campl-column8 campl-main-content" id="content">
	
	<h2>{$list.name|htmlspecialchars}</h2>
	
	{$list.detailsHtml}
	
	<div class="graybox">
		{if ($seminars)}
		<ul class="spaced small">
		{foreach from=$seminars item=seminar}
			<li><strong>{$seminar.date}</strong>:<br />{$seminar.title|htmlspecialchars} <a href="{$seminar.url}">Details&hellip;</a></li>
		{/foreach}
		</ul>
		{else}
		<p>There are no forthcoming seminars scheduled at present.</p>
		{/if}
	</div>
	
</div>


<div class="campl-column4 campl-secondary-content">
	<div class="campl-content-container">
		
		<div class="campl-heading-container">
			<h2>More details</h2>
		</div>
		
		<ul class="campl-unstyled-list campl-related-links">
			<li><a href="{$list.talksdotcamUrl}">More info on talks.cam</a></li>
			<li>Info</li>
		</ul>
		
	</div>
</div>
