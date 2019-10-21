{foreach from=$recommendations.data item=recommendation}
    <tr class="recommendation">
        <td  class="recommendation name">
            <h5>
                <a>
                    <a href="{$WWWROOT}artefact/file/download.php?file={$recommendation->file_id}">
                        <span class="icon icon-file icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                        {$recommendation->pub_name}</a>
                </a>
            </h5>
        </td>
        <td> {$recommendation->ranking} 
        </td>
        <td class="publicationcontrols control-buttons text-right">
            <div class="btn-group">
                <a href="{$WWWROOT}artefact/library/review.php?publication={$recommendation->pub_id}" class="btn btn-secondary btn-sm" title='{str tag=writeareview section=artefact.library}'>
                    <span class="icon icon-comment-o icon-lg" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{str tag=writeareview section=artefact.library}</span>
                </a>
            </div>
        </td>

    </tr>
{/foreach}




