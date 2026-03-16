**Español** | [English](/README.md) | [Português Brasileiro](/docs/README-pt_BR.md)

# Módulo Referencias para Capítulos

Este módulo permite agregar referencias para capítulos de monografías en OMP.

## Compatibilidad

La versión más reciente de este módulo es compatible con las siguientes aplicaciones PKP:

* OMP 3.4.0

## Descarga del módulo

Para descargar el módulo, acceda a la [Página de Versiones](https://github.com/lepidus/referencesForChapters/releases) y descargue el paquete tar.gz de la versión más reciente compatible con su sitio web.

## Instalación

1. Acceda al área de administración de su sitio OMP a través del __Panel de Control__.
2. Navegue hasta `Ajustes` > `Sitio web` > `Módulos` > `Cargar un nuevo módulo`.
3. En __Cargar archivo__, seleccione el archivo __referencesForChapters.tar.gz__.
4. Haga clic en __Guardar__ y el módulo se instalará en su sitio web.

## Uso

Después de instalar y habilitar el módulo, se mostrará un nuevo campo en el formulario utilizado para crear/editar capítulos. Su funcionamiento es similar al campo de referencias de la monografía.

![Referencias en el formulario de capítulo](../assets/references_on_chapter_form.png)

---

El módulo agrega una nueva sección a la página del capítulo, mostrando las referencias del capítulo al final de la sección principal. Esto solo ocurre si se está utilizando el Tema Predeterminado, como se muestra a continuación.

![Referencias en la página del capítulo](../assets/references_chapter_page.png)

Para mostrar las referencias del capítulo en la página del capítulo con otros temas, es necesario realizar un pequeño ajuste en el tema OMP utilizado. Deberá agregar el siguiente fragmento de código al archivo `templates/frontend/objects/chapter.tpl`, añadiéndolo en la posición en la que desea mostrar las referencias.

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

# Licencia
__Este módulo está licenciado bajo la GNU General Public License v3.0__

__Copyright (c) 2025 - 2026 Lepidus Tecnologia__
