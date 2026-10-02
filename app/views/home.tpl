
<div class="twocolumns">
	<div class="maincolumn">

		{if $userIsAdministrator}
			<p class="actions right"><a href="{$baseUrl}/data/lists/"><img src="/images/icons/pencil.png" class="icon" /> Edit lists</a></p>
		{/if}
		
		{foreach from=$listBoxesByCategory key=category item=listBoxes}
		<h3>{$category|escape}</h3>
		{$listBoxes}
		{/foreach}
		
		{if ($archivedLists)}
		<h2>Previous seminar series</h2>
		<ul>
		{foreach from=$archivedLists item=list}
			<li><a href="{$list.link}">{$list.name|escape}</a></li>
		{/foreach}
		</ul>
		{/if}
		
	</div>


	<div class="sidebarcolumn">
		
		<h2>Forthcoming seminars</h2>
		<ul>
			<li><a href="{$baseUrl}/calendar/">Listing with full details</li></li>
			{if (isset ($seminarsIcal))}
			<li><a href="{$seminarsIcal}"><img src="/images/icons/date.png" class="icon" /> Add to calendar</a></li>
			{/if}
		</ul>
		
		{if ($seminars)}
			<ul class="spaced small">
			{foreach from=$seminars item=seminar}
				<li><strong>{$seminar.date}</strong>:<br />{$seminar.title|escape} <a href="{$seminar.link}">Details&hellip;</a></li>
			{/foreach}
			</ul>
		{else}
			{if (isset ($error))}
				<p class="warning">{$error|escape}</p>
			{else}
				<p>There are no forthcoming seminars scheduled at present.</p>
			{/if}
		{/if}
		
	</div>
</div>
