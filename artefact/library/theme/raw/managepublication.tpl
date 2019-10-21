{include file="header.tpl"}
<div>
    <h4>
        <span class="icon icon-download icon-lg text-default left" role="presentation" aria-hidden="true"></span>
        <a href="{$WWWROOT}artefact/file/download.php?file={$fileid}">
            {$filename}
        </a>
    </h4>
</div>
<div id="librarywrap">
    {$publicationform|safe}
</div>
{include file="footer.tpl"}