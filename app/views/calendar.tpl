

<div class="campl-wp-content">
	
	<h2>Calendar</h2>
	
	{if (isSet ($seminarsIcal))}
	<ul>
		<li><a href="{$seminarsIcal}"><img src="/images/icons/date.png" class="icon" /> Add to calendar</a></li>
	</ul>
	{/if}
	
	{if ($seminarsByDate)}
	{foreach from=$seminarsByDate key=date item=seminars}
		<h3>{$date}</h3>
		<table class="calendar graybox">
		{foreach from=$seminars item=seminar}
			<tr><td style="overflow: auto">
				<h4>{$seminar.series|htmlspecialchars}</h4>
				<h5><em>{$seminar.title|htmlspecialchars}</em></h5>
				{if ($seminar.special_message)}
					<p class="specialmessage">{$seminar.special_message|htmlspecialchars}</p>
				{/if}
				<p>{$seminar.speaker|htmlspecialchars}<br />
				{$seminar.time}<br />
				{$seminar.venue|htmlspecialchars}</p>
				{$seminar.abstractHtml}
			</td></tr>
		{/foreach}
		</table>
	{/foreach}
	{else}
		<p>There are no forthcoming seminars scheduled at present.</p>
	{/if}
	
</div>


<div class="campl-wp-sidebar">
	
	<p>Switch to:</p>
	{$droplist}
	
	<h2>More details</h2>
	
	<ul>
		<li><a href="{$list.talksdotcamUrl}">More info on talks.cam</a></li>
		<li><a href="{$list.talksdotcamIcal}"><img src="/images/icons/date.png" class="icon" /> Add to your calendar</a></li>
	</ul>
	
</div>
