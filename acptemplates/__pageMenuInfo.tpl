<script data-relocate="true" src="{@$__wcf->getPath('cms')}acp/js/CMS.ACP{if !ENABLE_DEBUG_MODE}.min{/if}.js"></script>
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.pageMenu.parameters.notice': '{lang}wcf.acp.pageMenu.parameters.notice{/lang}'
		});
		new CMS.ACP.Page.Menu();
	});
	//]]>
</script>
