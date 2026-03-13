{* Chapter references *}
{if $chapterCitations || $chapter->getData('chapterCitationsRaw')}
    <div class="item references">
        <h2 class="label">
            {translate key="submission.citations"}
        </h2>
        <div class="value">
            {if $chapterCitations}
                {foreach from=$chapterCitations item=$chapterCitation}
                    <p>{$chapterCitation->getCitationWithLinks()|strip_unsafe_html}</p>
                {/foreach}
            {else}
                {$chapter->getData('chapterCitationsRaw')|escape|nl2br}
            {/if}
        </div>
    </div>
{/if}
