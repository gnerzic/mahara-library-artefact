{include file="header.tpl"}
<div class="btn-group btn-group-top">
    {if $library.canmanagelibrary}
        <a class="btn btn-secondary" href="{$WWWROOT}artefact/library/managelibrary.php?group={$library.group}">
            <span class="icon icon-files-o icon-lg left" role="presentation" aria-hidden="true"></span>
            {str section="artefact.library" tag="managelibrary"}
        </a>
    {/if}
</div>
{if !$library.data}
    <div class="no-results">{$strnolibrary|safe}</div>
{else}
    <div id="publicationswrap" class="publication-wrapper view-container">
        <div class="table-responsive">
            <table id="publicationslist" class="publication-listing table table-striped text-small">
                <thead>
                    <tr>
                        <th>{str section="artefact.library" tag="publications"}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {$library.tablerows|safe}

                </tbody>
            </table>
        </div>
        {$library.pagination|safe}
    </div>
{/if}
{if !$recommendations.data}
    <div class="no-results">{$strnorecommendations|safe}</div>
{else}
    <div id="recommendationswrap" class="recommendations-wrapper view-container">
        <div class="table-responsive">
            <table id="recommendationslist" class="recommendation-listing table table-striped text-small">
                <p class="lead">{str section="artefact.library" tag="recommendationslead"}</p>
                <thead>
                    <tr>
                        <th width = "100%">{str section="artefact.library" tag="recommendations"}</th>
                        <th><span class="icon icon-trophy icon-lg text-default left" role="presentation" aria-hidden="true"></span></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {$recommendations.tablerows|safe}
                </tbody>
            </table>
        </div>
        {$recommendations.pagination|safe}
    </div>
{/if}
{if !$alternative}
    <div class="no-results">{$strnoalternaterecommendations|safe}</div>
{else}
    <div>
        <h4>{str section="artefact.library" tag="haveyouconsidered"}</h4>
        <h5>
            <span class="icon icon-question icon-lg text-default left" role="presentation" aria-hidden="true"></span>
            <a href="{$WWWROOT}artefact/file/download.php?file={$alternative_fileid}">
                {$alternative->get('title')}
            </a>
            <span class="icon icon-question icon-lg text-default left" role="presentation" aria-hidden="true"></span>
        </h5>
    </div>
{/if}
{include file="footer.tpl"}
