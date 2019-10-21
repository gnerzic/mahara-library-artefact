{include file="header.tpl"}
<div>
    <h4>
        <span class="icon icon-download icon-lg text-default left" role="presentation" aria-hidden="true"></span>
        <a href="{$WWWROOT}artefact/file/download.php?file={$publicationfileid}">
            {$publicationfilename}
        </a>
    </h4>
</div>

{$form|safe}
{include file="footer.tpl"}
