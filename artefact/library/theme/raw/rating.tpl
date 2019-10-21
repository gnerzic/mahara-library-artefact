{include file="header.tpl"}
<div id="ratingswrap" class="rating-wrapper view-container">
    <h4>
        <span class="icon icon-download icon-lg text-default left" role="presentation" aria-hidden="true"></span>
        <a href="{$WWWROOT}artefact/file/download.php?file={$ratings.publication_file_id}">
            {$ratings.publication_file_name}
        </a>

    </h4>
    <div id="rating selection">   
        <table>
            <tr>
                <td >
                    {if $rating == 6} 
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}" class="btn btn-secondary text-tiny">
                        {else}
                              <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}" class="btn  btn-outline-secondary text-tiny">
                            {/if}
                            {str section="artefact.library" tag="allratings"}
                        </a>
                </td>
                <td>
                    {if $rating == 5}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=5" class="btn  btn-secondary text-tiny">
                    {else}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=5" class="btn  btn-outline-secondary text-tiny">

                    {/if}
                            <span class="icon icon-star"></span>
                            <span class="icon icon-star"></span>
                            <span class="icon icon-star"></span>
                            <span class="icon icon-star"></span>
                            <span class="icon icon-star"></span>
                        </a>    
                </td>
                <td>
                   {if $rating == 4}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=4" class="btn  btn-secondary text-tiny">
                    {else}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=4" class="btn  btn-outline-secondary text-tiny">

                    {/if}
                        <span class="icon icon-star"></span>
                        <span class="icon icon-star"></span>
                        <span class="icon icon-star"></span>
                        <span class="icon icon-star"></span>
                        <span class="icon icon-star-o"></span>
                    </a>
                </td>
                <td >
                   {if $rating == 3}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=3" class="btn  btn-secondary text-tiny">
                    {else}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=3" class="btn  btn-outline-secondary text-tiny">

                    {/if}
                        <span class="icon icon-star"></span>
                        <span class="icon icon-star"></span>
                        <span class="icon icon-star"></span>
                        <span class="icon icon-star-o"></span>
                        <span class="icon icon-star-o"></span>
                    </a>
                </td>
                <td>
                   {if $rating == 2}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=2" class="btn  btn-secondary text-tiny">
                    {else}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=2" class="btn  btn-outline-secondary text-tiny">

                    {/if}

                        <span class="icon icon-star"></span>
                        <span class="icon icon-star"></span>
                        <span class="icon icon-star-o"></span>
                        <span class="icon icon-star-o"></span>
                        <span class="icon icon-star-o"></span>
                    </a>
                </td>
                <td>
                    {if $rating == 1}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=1" class="btn  btn-secondary text-tiny">
                    {else}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=1" class="btn  btn-outline-secondary text-tiny">

                    {/if}

                        <span class="icon icon-star"></span>
                        <span class="icon icon-star-o"></span>
                        <span class="icon icon-star-o"></span>
                        <span class="icon icon-star-o"></span>
                        <span class="icon icon-star-o"></span>
                    </a>
                </td>
                <td>
                   {if $rating == 0}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=0" class="btn  btn-secondary text-tiny">
                    {else}
                        <a href="{$WWWROOT}artefact/library/rating.php?publication={$publication}&rating=0" class="btn  btn-outline-secondary text-tiny">

                    {/if}
                        <span class="icon icon-star-o"></span>
                        <span class="icon icon-star-o"></span>
                        <span class="icon icon-star-o"></span>
                        <span class="icon icon-star-o"></span>
                        <span class="icon icon-star-o"></span>
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-responsive">
        <table id="ratingslist" class="rating-listing table table-striped text-small">
            <thead>
                <tr>
                    <th >{str section="artefact.library" tag="rating"}</th>
                    <th width = "100%">{str section="artefact.library" tag="review"}</th>
                    <th>{str section="artefact.library" tag="author"}</th>
                </tr>
            </thead>
            <tbody>
                {$ratings.tablerows|safe}

            </tbody>
        </table>
    </div>
    {$ratings.pagination|safe}
</div>
    
{if $similar_items.data}
    <div id="similaritemswrap" class="similaritems-wrapper view-container">
        <div class="table-responsive">
            <table id="similaritemslist" class="similaritem-listing table table-striped text-small">
                <p class="lead">{str section="artefact.library" tag="similaritemslead"}</p>
                <thead>
                    <tr>
                        <th width = "100%">{str section="artefact.library" tag="publications"}</th>
                        <th><span class="icon icon-percent icon-lg text-default left" role="presentation" aria-hidden="true"></span></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {$similar_items.tablerows|safe}
                </tbody>
            </table>
        </div>
        {$similar_items.pagination|safe}
    </div>
{/if}
    
{include file="footer.tpl"}