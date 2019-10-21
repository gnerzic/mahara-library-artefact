{foreach from=$library.data item=publication}
    <tr class="publication">
        <td  class="publication filename">
            <h5>
                <a href="{$WWWROOT}artefact/file/download.php?file={$publication->fileid}"><span class="icon icon-file icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                    {$publication->title}
                </a>
            </h5>
            {$publication->description|clean_html|safe}
            <h6>
                <b>{str tag=rating section=artefact.library}</b>: 

                {if $publication->rating == -1}
                    <i> {str tag=notyetrated section=artefact.library}</i>
                {/if}
                {if $publication->rating != -1}
                    <a title="{$publication->rating -1}">
                        <!-- first start -->
                        {if $publication->rating <= 1} 
                            <span class="icon icon-star-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        {if $publication->rating > 1 & $publication->rating < 2}
                            <span class="icon icon-star-half-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        {if $publication->rating >= 2}
                            <span class="icon icon-star icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        <!-- endof first start-->

                        <!-- second start -->
                        {if $publication->rating <= 2} 
                            <span class="icon icon-star-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        {if $publication->rating > 2 & $publication->rating < 3}
                            <span class="icon icon-star-half-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        {if $publication->rating >= 3}
                            <span class="icon icon-star icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}

                        <!-- third start -->
                        {if $publication->rating <= 3} 
                            <span class="icon icon-star-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        {if $publication->rating > 3 & $publication->rating < 4}
                            <span class="icon icon-star-half-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        {if $publication->rating >= 4}
                            <span class="icon icon-star icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}

                        <!-- forth start -->
                        {if $publication->rating <= 4} 
                            <span class="icon icon-star-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        {if $publication->rating > 4 & $publication->rating < 5}
                            <span class="icon icon-star-half-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        {if $publication->rating >= 5}
                            <span class="icon icon-star icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}       

                        <!-- fifthj start -->
                        {if $publication->rating <= 5} 
                            <span class="icon icon-star-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        {if $publication->rating > 5 & $publication->rating < 6}
                            <span class="icon icon-star-half-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}
                        {if $publication->rating >= 6}
                            <span class="icon icon-star icon-sm text-default" role="presentation" aria-hidden="true"></span>
                        {/if}</a>
                    (<a href="{$WWWROOT}artefact/library/rating.php?publication={$publication->id}" title = '{str tag=seeratingdetail section=artefact.library}'>{$publication->rating_volume})</a>
                {/if}
            </h6>
            {if $publication->tags}
                <span>{str tag=tags}: </span>
                {list_tags owner=$publication->owner tags=$publication->tags}
            {/if}
        </td>
        <td class="publicationcontrols control-buttons text-right">
            <div class="btn-group">

                {if $publication->review}
                    <a href="{$WWWROOT}artefact/library/review.php?id={$publication->review->get('id')}" class="btn btn-secondary btn-sm" title = '{str tag=edityourreview section=artefact.library}'>
                        <span class="icon icon-commenting-o text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only"> {str tag=edityourreview section=artefact.library}</span> 
                    </a>
                    <a href="{$WWWROOT}artefact/library/removereview.php?id={$publication->review->get('id')}" class="btn btn-secondary btn-sm" title ='{str tag=removeyourreview section=artefact.library}'>
                        <span class="icon icon-trash-o text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=removeyourreview section=artefact.library}</span>
                    </a>                    
                {else}
                    <a href="{$WWWROOT}artefact/library/review.php?publication={$publication->id}" class="btn btn-secondary btn-sm" title='{str tag=writeareview section=artefact.library}'>
                        <span class="icon icon-comment-o icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=writeareview section=artefact.library}</span>
                    </a>

                {/if}

                {if $library.canmanagelibrary}
                    <a href="{$WWWROOT}artefact/library/publication.php?id={$publication->id}" class="btn btn-secondary btn-sm" title="{str tag=managepublication section=artefact.library}">
                        <span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=managepublication section=artefact.library}</span>
                    </a>
                {/if}              

            </div>
        </td>
    </tr>
{/foreach}




