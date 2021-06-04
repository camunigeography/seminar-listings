

<div class="campl-column9 campl-main-content" id="content">
	<div class="campl-content-container">
		
		<h2>Seminar series</h2>
		<div class="clearfix">
		{foreach from=$lists item=list}
			<div class="campl-column6">
				<div class="campl-content-container">
					<div class="campl-horizontal-teaser campl-teaser clearfix campl-focus-teaser">
						<div class="campl-focus-teaser-img">
							<div class="campl-content-container campl-horizontal-teaser-img"><a class="campl-teaser-img-link noautoarrow noautoicon" href="{$list.link}"><img alt="" class="campl-scale-with-grid" src="{$list.thumbnail}" /></a></div>
						</div>
						<div class="campl-focus-teaser-txt">
							<div class="campl-content-container campl-horizontal-teaser-txt">
								<h3 class="campl-teaser-title"><a href="{$list.link}" class="noautoarrow noautoicon">{$list.name|htmlspecialchars}</a></h3>
								<a class="ir campl-focus-link noautoarrow" href="{$list.link}">Read more</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/foreach}
		</div>
		
		{if ($archivedLists)}
		<h2>Previous seminar series</h2>
		<ul>
		{foreach from=$archivedLists item=list}
			<li><a href="{$list.link}">{$list.name|htmlspecialchars}</a></li>
		{/foreach}
		</ul>
		{/if}
		
	</div>
</div>


<div class="campl-column3 campl-secondary-content">
	<div class="campl-content-container">
		
		<div class="campl-heading-container">
			<h2>Title</h2>
		</div>
		
	</div>
</div>
