
<div class="twocolumns">
	<div class="maincolumn">

		{if $userIsAdministrator}
			<p class="actions right"><a href="{$baseUrl}/data/lists/"><img src="/images/icons/pencil.png" class="icon" /> Edit lists</a></p>
		{/if}
		
		{foreach from=$listsByCategory key=category item=lists}
		<h3>{$category|escape}</h3>
		<div class="clearfix">
			{foreach from=$lists item=list name=lists}
			<div class="campl-column6">
				<div class="campl-content-container">
					<div class="campl-horizontal-teaser campl-teaser clearfix campl-focus-teaser">
						<div class="campl-focus-teaser-img">
							<div class="campl-content-container campl-horizontal-teaser-img"><a class="campl-teaser-img-link noautoarrow noautoicon" href="{$list.link}"><img alt="" class="campl-scale-with-grid" src="{$list.thumbnail}" /></a></div>
						</div>
						<div class="campl-focus-teaser-txt">
							<div class="campl-content-container campl-horizontal-teaser-txt">
								<h3 class="campl-teaser-title"><a href="{$list.link}" class="noautoarrow noautoicon">{$list.name|escape}</a></h3>
								<a class="ir campl-focus-link noautoarrow" href="{$list.link}">Read more</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			{if $smarty.foreach.lists.iteration is div by 2}
			</div>
			<div class="clearfix">
			{/if}
			{/foreach}
		</div>
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
