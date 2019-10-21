{foreach from=$similar_items.data item=similar_item}
    <tr class="similaritem">
        <td  class="similaritem name">
            <h5>
                <a>
                    <a href="{$WWWROOT}artefact/file/download.php?file={$similar_item->file_id}">
                        <span class="icon icon-file icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                        {$similar_item->pub_name}</a>
                </a>
            </h5>
        </td>
        <td> {$similar_item->similary} 
        </td>
        
         <td class="similaritemcontrols control-buttons text-right">
            <div class="btn-group">
                {if $similar_item->user_rating}
                    <a href="{$WWWROOT}artefact/library/review.php?id={$similar_item->user_rating->get('id')}" class="btn btn-secondary btn-sm" title = '{str tag=edityourreview section=artefact.library}'>
                        <span class="icon icon-commenting-o text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only"> {str tag=edityourreview section=artefact.library}</span> 
                    </a>
                    <a href="{$WWWROOT}artefact/library/removereview.php?id={$similar_item->user_rating->get('id')}" class="btn btn-secondary btn-sm" title ='{str tag=removeyourreview section=artefact.library}'>
                        <span class="icon icon-trash-o text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=removeyourreview section=artefact.library}</span>
                    </a>                    
                {else}
                    <a href="{$WWWROOT}artefact/library/review.php?publication={$similar_item->pub_id}" class="btn btn-secondary btn-sm" title='{str tag=writeareview section=artefact.library}'>
                        <span class="icon icon-comment-o icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=writeareview section=artefact.library}</span>
                    </a>

                {/if}
            </div>
        </td>

    </tr>
{/foreach}




