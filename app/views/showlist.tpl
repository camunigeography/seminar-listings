
<div class="twocolumns">
	<div class="maincolumn">

		<h2>{$list.name|escape}</h2>
		
		{if $isEditor}
		<div class="clearfix">
			<p class="primaryaction right"><a href="https://talks.cam.ac.uk/list/edit/{$list.talksdotcamListNumber}/" title="Edit the list details, on talks.cam"><img src="/images/icons/pencil.png" class="icon" /> Edit list description</a></p>
		</div>
		{/if}
		
		<div class="details">
			{$list.detailsHtml}
		</div>
		
		{if $isEditor}
		<div class="clearfix">
			<p class="primaryaction right"><a href="https://talks.cam.ac.uk/talk/new/{$list.talksdotcamListNumber}/" title="Add a new talk to the seminars listing, on talks.cam"><img src="/images/icons/pencil.png" class="icon" /> Add seminar</a></p>
		</div>
		{/if}
		
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
							<div>{$seminar.title|escape}</div>
						</div>
					</div>
				</h2>
				{if ($seminar.special_message)}
					<p class="specialmessage">{$seminar.special_message|escape}</p>
				{/if}
				<p><strong>Speaker:</strong> {$seminar.speaker|escape}</p>
				<p><strong>Time:</strong> {$seminar.time}</p>
				<p><strong>Location:</strong> {$seminar.venue|escape}</p>
				{$seminar.abstractHtml}
			</div>
				
			{/foreach}
			
			{else}
				{if (isset ($errorSeminars))}
					<p class="warning">{$errorSeminars|escape}</p>
				{else}
					<div class="graybox">
						<p><strong>There are no forthcoming seminars scheduled at present.</strong></p>
					</div>
				{/if}
			{/if}
			
		{/if}
		
		{if ($archived)}
		<h3 id="previous">Previous seminars</h3>
		<div class="graybox">
			<ul class="spaced small">
			{foreach from=$archived item=seminar}
				<li id="id{$seminar.id}"><strong>{$seminar.date} - {$seminar.speaker|escape}</strong>:<br />{$seminar.title|escape}. <a href="{$seminar.url}">Details&hellip;</a></li>
			{/foreach}
			</ul>
		</div>
		{/if}
		{if (isset ($errorArchived))}
			{if (!isset ($errorSeminars))}	{* Don't show error message twice (even though a different feed URL) *}
				<p class="warning">{$errorArchived|escape}</p>
			{/if}
		{/if}
		
	</div>


	<div class="sidebarcolumn">
		
		{if $droplist}
		<p>Switch to:</p>
		{$droplist}
		{/if}
		
		{if $administrator}
		<div class="clearfix">
			<p class="primaryaction right"><a href="{$baseUrl}/data/lists/{$list.id}/edit.html" title="Edit the seminars listing, on talks.cam"><img src="/images/icons/pencil.png" class="icon" /> Edit list status</a></p>
		</div>
		{/if}
		
		<h2>More details</h2>
		
		<ul>
			<li><a href="{$list.talksdotcamUrl}">More info on talks.cam</a></li>
			<li><a href="{$list.talksdotcamIcal}"><img src="/images/icons/date.png" class="icon" /> Add to your calendar</a></li>
		</ul>
		
	</div>
</div>
