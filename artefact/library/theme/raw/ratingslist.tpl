{foreach from=$ratings.data item=rating}
    <tr class="rating">
        <td style="white-space:nowrap"  class="rating value">
            <!-- first star -->
            {if $rating->rating > 1} 
                <span class="icon icon-star icon-sm text-default" role="presentation" aria-hidden="true"></span>
            {else}
                <span class="icon icon-star-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
            {/if}
            <!-- endof first star-->
            
            <!-- second star -->
            {if $rating->rating > 2} 
                <span class="icon icon-star icon-sm text-default" role="presentation" aria-hidden="true"></span>
            {else}
                <span class="icon icon-star-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
            {/if}
            <!-- endof second star-->
            
            <!-- third star -->
            {if $rating->rating > 3} 
                <span class="icon icon-star icon-sm text-default" role="presentation" aria-hidden="true"></span>
            {else}
                <span class="icon icon-star-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
            {/if}
            <!-- endof third star-->

             <!-- second fourth -->
            {if $rating->rating > 4} 
                <span class="icon icon-star icon-sm text-default" role="presentation" aria-hidden="true"></span>
            {else}
                <span class="icon icon-star-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
            {/if}
            <!-- endof fourth star-->

             <!-- second fifth -->
            {if $rating->rating > 5} 
                <span class="icon icon-star icon-sm text-default" role="presentation" aria-hidden="true"></span>
            {else}
                <span class="icon icon-star-o icon-sm text-default" role="presentation" aria-hidden="true"></span>
            {/if}
            <!-- endof fifth star-->            
        </td>
        <td class="rating title">
            <h5>{$rating->title}</h5>
            {$rating->description|safe}
            
        </td>
        <td class="rating author">
            <a href="{$WWWROOT}user/view.php?id={$rating->author_id}" title="{str section="artefact.library" tag="profilepage"}">
            <img src="{$rating->author_pic_url}" alt ="{$rating->author_name}"/><br/>
            {$rating->author_name}
            </a>
        </td>
    </tr>
{/foreach}




