

<div class="campl-column8 campl-main-content" id="content">
	
	<h2>{$list.name|htmlspecialchars}</h2>
	
	{if $administrator}
	<div class="clearfix">
		<p class="primaryaction right"><a href="https://talks.cam.ac.uk/list/edit/{$list.talksdotcamListNumber}" title="Edit the seminars listing, on talks.cam"><img src="/images/icons/pencil.png" class="icon" /> Edit seminars</a></p>
	</div>
	{/if}
	
	<div class="details">
		{$list.detailsHtml}
	</div>
	
	{if !$list.archived}
		
		{if ($seminars)}
		{foreach from=$seminars item=seminar}
			
			<div class="graybox" id="id{$seminar.id}">
				<h2>
					<div>
					<div class="campl-highlight-event-item clearfix">
						<div class="campl-highlight-date-container">
							<div class="campl-highlight-date">
								<div class="campl-highlight-day">{$seminar.day}</div>{$seminar.month}
							</div>
						</div>
						<div>{$seminar.title|htmlspecialchars}</div>
					</div>
				</div>
			</h2>
			{if ($seminar.special_message)}
				<p class="specialmessage">{$seminar.special_message|htmlspecialchars}</p>
			{/if}
			<p><strong>Speaker:</strong> {$seminar.speaker|htmlspecialchars}</p>
			<p><strong>Time:</strong> {$seminar.time}</p>
			<p><strong>Location:</strong> {$seminar.venue|htmlspecialchars}</p>
			<p class="smaller">{$seminar.abstract|htmlspecialchars}</p>
		</div>
			
		{/foreach}
		
		{else}
			<div class="graybox">
				<p><strong>There are no forthcoming seminars scheduled at present.</strong></p>
			</div>
		{/if}
		
	{/if}
	
	{if ($archived)}
	<h3 id="previous">Previous seminars</h3>
	<div class="graybox">
		<ul class="spaced small">
		{foreach from=$archived item=seminar}
			<li id="id{$seminar.id}"><strong>{$seminar.date} - {$seminar.speaker|htmlspecialchars}</strong>:<br />{$seminar.title|htmlspecialchars}. <a href="{$seminar.url}">Details&hellip;</a></li>
		{/foreach}
		</ul>
	</div>
	{/if}
	
</div>


<div class="campl-column4 campl-secondary-content">
	<div class="campl-content-container">
		
		{if $droplist}
		<p>Switch to list:</p>
		{$droplist}
		{/if}
		
		{if $administrator}
		<div class="clearfix">
			<p class="primaryaction right"><a href="{$baseUrl}/data/lists/{$list.id}/edit.html" title="Edit the seminars listing, on talks.cam"><img src="/images/icons/pencil.png" class="icon" /> Edit list info</a></p>
		</div>
		{/if}
		
		<div class="campl-heading-container">
			<h2>More details</h2>
		</div>
		
		<ul class="campl-unstyled-list campl-related-links">
			<li><a href="{$list.talksdotcamUrl}">More info on talks.cam</a></li>
		</ul>
		
	</div>
</div>
