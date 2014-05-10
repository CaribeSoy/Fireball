{capture assign='pageTitle'}{lang}cms.acp.content.{@$action}{/lang}{/capture}
{include file='header'}

{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}

<nav class="breadcrumbs marginTop">
	<ul>
		<li title="{lang}cms.acp.page.overview{/lang}" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
			<a href="{link controller='Overview' application='cms'}{/link}" itemprop="url">
				<span itemprop="title">{lang}cms.acp.page.overview{/lang}</span>
			</a>
			<span class="pointer">
				<span>�</span>
			</span>
		</li>

    {if $pageID != 0}
		<li title="{$page->getTitle()|language}" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb">
			<a href="{link controller='PageEdit' application='cms' id=$page->pageID}{/link}" itemprop="url">
				<span itemprop="title">{$page->getTitle()|language}</span>
			</a>
			<span class="pointer">
				<span>�</span>
			</span>
		</li>
	{/if}
	</ul>
</nav>

<header class="boxHeadline">
    <h1>{lang}cms.acp.content.{@$action}{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
<p class="success">{lang}wcf.global.success.{@$action}{/lang}</p>
{/if}

<div class="contentNavigation">
    <nav>
        <ul>
			<li><a href="{link controller='Overview' application='cms'}{/link}" class="button"><span class="icon icon16 icon-gears"></span> <span>{lang}cms.acp.page.overview{/lang}</span></a></li>
			{if $contentID|isset}<li><a href="{link application='cms' controller='ContentSectionList' id=$contentID}{/link}" title="{lang}cms.acp.content.content.section.list{/lang}" class="button"><span class="icon icon16 icon-list-alt"></span> <span>{lang}cms.acp.content.content.section.list{/lang}</span></a></li>{/if}
            <li><a href="{link application='cms' controller='ContentList'}{/link}" title="{lang}cms.acp.menu.link.cms.content.list{/lang}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}cms.acp.menu.link.cms.content.list{/lang}</span></a></li>

            {event name='contentNavigationButtons'}
        </ul>
    </nav>
</div>
{if $pageList|count}
<form method="post" action="{if $action == 'add'}{link application='cms' controller='ContentAdd' id=$pageID}objectType={$objectType->objectType}{/link}{else}{link application='cms' controller='ContentEdit' id=$contentID}objectType={$objectType->objectType}{/link}{/if}">
    <div class="container containerPadding marginTop shadow">
        <fieldset>
            <legend>{lang}cms.acp.content.general{/lang}</legend>
            <dl {if $errorField == 'title'}class="formError"{/if}>
                <dt><label for="title">{lang}cms.acp.content.general.title{/lang}</label></dt>
                <dd>
                    <input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" class="long" required="required" />
                    {if $errorField == 'title'}
                        <small class="innerError">
                            {if $errorType == 'empty'}
                            {lang}wcf.global.form.error.empty{/lang}
                            {else}
                            {lang}cms.acp.content.title.error.{@$errorType}{/lang}
                            {/if}
                        </small>
                    {/if}
                </dd>
            </dl>
        </fieldset>

        <fieldset>
        	<legend></legend>
        	{include file=$objectTypeProcessor->getFormTemplate() application='cms'}
        </fieldset>

        <fieldset>
            <legend>{lang}cms.acp.content.css{/lang}</legend>
            <dl>
                <dt><label for="cssID">{lang}cms.acp.content.css.cssID{/lang}</label></dt>
                <dd>
                    <input type="text" id="cssID" name="cssID" value="{$cssID}" class="long" />
                    {if $errorField == 'cssID'}
                        <small class="innerError">
                            {lang}cms.acp.content.cssID.error.{@$errorType}{/lang}
                        </small>
                    {/if}
                </dd>
            </dl>
            <dl>
                <dt><label for="cssClasses">{lang}cms.acp.content.css.cssClasses{/lang}</label></dt>
                <dd>
                     <input type="text" id="cssClasses" name="cssClasses" value="{$cssClasses}" class="long" />
						<small class="description">
                            {lang}cms.acp.content.css.cssClasses.description{/lang}
                        </small>
					{if $errorField == 'cssClasses'}
                        <small class="innerError">
                            {lang}cms.acp.content.cssClasses.error.{@$errorType}{/lang}
                        </small>
                    {/if}
                </dd>
            </dl>
        </fieldset>
        <fieldset>
            <legend>{lang}cms.acp.content.position{/lang}</legend>
			<dl>
                <dt><label for="showOrder">{lang}cms.acp.content.position.showOrder{/lang}</label></dt>
                <dd>
                    <input type="number" name="showOrder" id="showorder" value="{$showOrder}" />
                </dd>
            </dl>
        </fieldset>
        {event name='fieldsets'}
    </div>
    <div class="formSubmit">
        <input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
        <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
         {@SECURITY_TOKEN_INPUT_TAG}
        <input type="hidden" name="action" value="{@$action}" />
        <input type="hidden" name="objectType" value="{@$objectType->objectType}" />
        {if $contentID|isset}<input type="hidden" name="contentID" value="{@$contentID}" />{/if}
        {if $pageID|isset}<input type="hidden" name="pageID" value="{@$pageID}" />{/if}
    </div>
</form>
{else}
<p class="info">{lang}cms.acp.content.add.noPage{/lang}</p>
{/if}
{include file='footer'}
