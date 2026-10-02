
	<div class="campl-wp-content">
		
		<h2>Calendar</h2>
		
		{if (isset ($seminarsIcal))}
		<ul>
			<li><a href="{$seminarsIcal}"><img src="/images/icons/date.png" class="icon" /> Add to calendar</a></li>
		</ul>
		{/if}
		
		{if ($seminarsByDate)}
		{foreach from=$seminarsByDate key=date item=seminars}
			<h3>{$date}</h3>
			<table class="calendar graybox">
			{foreach from=$seminars item=seminar}
				<tr id="id{$seminar.id}"><td>
					<h4>{$seminar.series|escape}</h4>
					<h5><em>{$seminar.title|escape}</em></h5>
					{if ($seminar.special_message)}
						<p class="specialmessage">{$seminar.special_message|escape}</p>
					{/if}
					<p>{$seminar.speaker|escape}<br />
					{$seminar.time}<br />
					{$seminar.venue|escape}</p>
					{$seminar.abstractHtml}
				</td></tr>
			{/foreach}
			</table>
		{/foreach}
		{else}
			{if (isset ($error))}
				<p class="warning">{$error|escape}</p>
			{else}
				<p>There are no forthcoming seminars scheduled at present.</p>
			{/if}
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
