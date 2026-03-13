# References for Chapters Plugin 

This plugin allows the adding of references for monograph chapters in OMP.

## Compatibility

The latest release of this plugin is compatible with the following PKP applications:

* OMP 3.4.0

## Plugin Download

To download the plugin, go to the [Releases page](https://github.com/lepidus/referencesForChapters/releases) and download the tar.gz package of the latest release compatible with your website.

## Installation

1. Enter the administration area of ​​your OMP website through the __Dashboard__.
2. Navigate to `Settings`>` Website`> `Plugins`> `Upload a new plugin`.
3. Under __Upload file__ select the file __referencesForChapters.tar.gz__.
4. Click __Save__ and the plugin will be installed on your website.

## Usage

After installing and enabling the plugin, a new field will be displayed in the form used to create/edit chapters. Its functioning is similar to the submission's references field.

![References on chapter form](assets/references_on_chapter_form.png)

---

The plugin adds a new section to the chapter page, displaying the chapter references at the end of the main section. This only happens if the Default Theme is being used, as shown below.

![References on chapter page](assets/references_chapter_page.png)

To display the chapter references in the chapter page for other themes, it's necessary to make a small adjustment to the OMP theme being used. You'll need to add the following strip of code to the `templates/frontend/objects/chapter.tpl` file, adding it to the position you want to exhibit the references.

```smarty
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
```

# License
__This plugin is licensed under the GNU General Public License v3.0__

__Copyright (c) 2025 - 2026 Lepidus Tecnologia__
