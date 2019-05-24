<form action="<?= PluginEngine::getLink($plugin, array(), "lernmodule/edit/".$module->getId()) ?>"
      method="post"
      class="default"
      enctype="multipart/form-data">

    <fieldset>
        <legend><?= dgettext("lernmoduleplugin","Lernmodul hochladen und bearbeiten") ?></legend>

        <? if (Request::get("type")) : ?>
            <input type="hidden" name="upload_or_url" value="<?= htmlReady(Request::get("type")) ?>">
        <? else : ?>
            <div class="hgroup">
                <label>
                    <input type="radio"
                           name="upload_or_url"
                           onChange="jQuery('#file_upload').toggle(this.checked); jQuery('#lernmodul_url').toggle(!this.checked);"
                           value="upload"<?= !$module['url'] ? " checked" : "" ?>>
                    <?= dgettext("lernmoduleplugin","Hochladen") ?>
                </label>

                <label>
                    <input type="radio"
                           name="upload_or_url"
                           onChange="jQuery('#lernmodul_url').toggle(this.checked); jQuery('#file_upload').toggle(!this.checked);"
                           value="url"<?= $module['url'] ? " checked" : "" ?>>
                    <?= _("URL") ?>
                </label>
            </div>
        <? endif ?>

        <label class="file-upload" id="file_upload"<?= $module['url'] || Request::get("type") === "url" ? ' style="display: none;"' : "" ?>>
            <input type="file" name="modulefile" accept=".zip,.h5p,.pdf" onChange="if (!jQuery('#modulename').val()) { var name = this.files[0].name; jQuery('#modulename').val(name.lastIndexOf('.') === -1 ? name : name.substr(0, name.lastIndexOf('.'))); }">
            <?= sprintf(dgettext("lernmoduleplugin","Lernmodul auswählen (Gezipptes HTML, H5P oder PDF, maximal %s MB)"), floor(min(LernmodulePlugin::bytesFromPHPIniValue(ini_get('post_max_size')), LernmodulePlugin::bytesFromPHPIniValue(ini_get('upload_max_filesize'))) / 1024 / 1024)) ?>
        </label>

        <script>
            jQuery(function () {
                jQuery("#file_upload").on('dragover dragleave', function (event) {
                    jQuery(this).toggleClass('hovered', event.type === 'dragover');
                    return false;
                });
                jQuery("#file_upload").on('drop', function (event) {
                    jQuery(this).removeClass('hovered');
                    var file = event.originalEvent.dataTransfer.files[0]
                    jQuery("#file_upload input[name=modulefile]")[0].file = file;
                    if (jQuery(this).closest('label').find('.filename').length) {
                        var filename = $(this).closest('label').find('.filename');
                    } else {
                        var filename = $('<span class="filename"/>');
                        jQuery(this).closest('label').append(filename);
                    }
                    jQuery("#file_upload .filename").text(file.name + ' ' + Math.ceil(file.size / 1024) + 'KB');
                    //jQuery("#file_upload input[name=modulefile]").trigger("change"); why do I get a download with this??
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                });
            });
        </script>

        <label id="lernmodul_url"<?= (!$module->isNew() && !$module['url']) || Request::get("type") === "upload" ? ' style="display: none;"' : "" ?>>
            <?= dgettext("lernmoduleplugin","URL des Lernmoduls") ?>
            <input type="text"
                   name="module[url]"
                   placeholder="http://..."
                   value="<?= htmlReady($module['url']) ?>"
                   onChange="jQuery('#lernmarktplatz_publish').toggle(!this.value);">
        </label>

        <label>
            <?= dgettext("lernmoduleplugin","Name des Moduls") ?>
            <input type="text" id="modulename" name="module[name]" required value="<?= htmlReady($module['name']) ?>">
        </label>

        <? if (count($lernmodule)) : ?>
            <div style="margin-top: 15px; margin-bottom: 15px;">
                <?= dgettext("lernmoduleplugin","Abhängig von") ?>
                <ul class="clean" style="font-size: 0.8em;">
                    <? $dependencies = array_map(function ($dep) { return $dep['depends_from_module_id']; }, $module->getDependencies(Context::get()->id)) ?>
                    <? foreach ($lernmodule as $lernmodul) : ?>
                        <li>
                            <label>
                                <input type="checkbox" name="dependencies[]" value="<?= htmlReady($lernmodul->getId()) ?>"<?= in_array($lernmodul->getId(), $dependencies) ? " checked" : "" ?>>
                                <?= htmlReady($lernmodul['name']) ?>
                            </label>
                        </li>
                    <? endforeach ?>
                </ul>
            </div>
        <? endif ?>

        <? if (false && class_exists("LernmarktplatzMaterial")) : ?>
            <label id="lernmarktplatz_publish" style="<?= $module['url'] ? 'display: none; ' : '' ?>">
                <input type="checkbox" name="module[material_id]" value="<?= htmlReady($module['material_id'] ?: 1) ?>">
                <?= dgettext("lernmoduleplugin","Auf Lernmarktplatz veröffentlichen (unter CC-BY-SA für alle zum freien Download)") ?>
            </label>
        <? endif ?>
    </fieldset>

    <fieldset>
        <legend>
            <?= dgettext("lernmoduleplugin","Abspieloptionen") ?>
        </legend>

        <? if (!$module->isNew()) : ?>
            <? if ($module['url']) : ?>
                <label>
                    <?= dgettext("lernmoduleplugin","Adresse des Logos") ?>
                    <input type="text"
                           name="module[image]"
                           onChange="jQuery('#image_preview').css('background-image', 'url(' + this.value + ')');"
                           value="<?= htmlReady($module['image']) ?>"
                           placeholder="http://...">
                </label>

                <div id="image_preview" style="display: inline-block; vertical-align: middle; margin: 10px; border: white solid 4px; box-shadow: rgba(0,0,0,0.3) 0px 0px 7px; width: 300px; height: 100px; max-width: 300px; max-height: 100px; background-size: 100% auto; background-repeat: no-repeat; background-position: center center;<?= $module['image'] ? " background-image: url('".htmlReady($module['image'])."');" : "" ?>"></div>

            <? else : ?>
                <? $module_images = $module->scanForImages() ?>
                <? if (count($module_images) + count($course_images) > 0) : ?>
                    <label>
                        <?= dgettext("lernmoduleplugin","Bild auswählen") ?>
                        <select id="select_image" name="module[image]" onChange="STUDIP.Lernmodule.selectImage.call(this);">
                            <option value=""><?= dgettext("lernmoduleplugin","Keines") ?></option>
                            <? if (count($module_images)) : ?>
                                <optgroup label="<?= dgettext("lernmoduleplugin","Bilder aus dem Lernmodul") ?>">
                                    <? foreach ($module_images as $image) : ?>
                                        <option value="<?= htmlReady($image) ?>"
                                                data-url="<?= $module->getDataURL() ?>/<?= htmlReady($image) ?>"<?= $module['image'] === $image ? " selected" : "" ?>>
                                            <?= htmlReady($image) ?>
                                        </option>
                                    <? endforeach ?>
                                </optgroup>
                            <? endif ?>
                            <? if (count($course_images)) : ?>
                                <optgroup label="<?= dgettext("lernmoduleplugin","Bilder der Veranstaltung") ?>">
                                    <? foreach ($course_images as $fileref) : ?>
                                        <option value="<?= htmlReady($fileref->getId()) ?>" data-url="<?= htmlReady($fileref->getDownloadURL()) ?>"<?= $module['image'] === $fileref->getId() ? " selected" : "" ?>>
                                            <?= htmlReady($fileref['name']) ?>
                                        </option>
                                    <? endforeach ?>
                                </optgroup>
                            <? endif ?>


                        </select>
                    </label>
                    <div>
                        <a href="" onClick="STUDIP.Lernmodule.selectPreviousImage(); return false;">
                            <?= Icon::create("arr_1left", "clickable")->asImg(20, array('style' => "vertical-align: middle;")) ?>
                        </a>
                        <? $background_image = FileRef::find($module['image']) ?: $module->getDataURL()."/".$module['image'] ?>
                        <div id="image_preview" style="display: inline-block; vertical-align: middle; margin: 10px; border: white solid 4px; box-shadow: rgba(0,0,0,0.3) 0px 0px 7px; width: 300px; height: 100px; max-width: 300px; max-height: 100px; background-size: 100% auto; background-repeat: no-repeat; background-position: center center;<?= $module['image'] ? " background-image: url('".htmlReady(is_a($background_image, "FileRef") ? $background_image->getDownloadURL() : $background_image)."');" : "" ?>"></div>
                        <a href="" onClick="STUDIP.Lernmodule.selectNextImage(); return false;">
                            <?= Icon::create("arr_1right", "clickable")->asImg(20, array('style' => "vertical-align: middle;")) ?>
                        </a>
                    </div>
                <? endif ?>
            <? endif ?>
        <? endif ?>

        <label>
            <input type="hidden" name="modulecourse[anonymous_attempts]" value="0">
            <input type="checkbox" name="modulecourse[anonymous_attempts]" value="1"<?= $modulecourse['anonymous_attempts'] ? " checked" : "" ?>>
            <?= dgettext("lernmoduleplugin","Nutzer sollen anonym teilnehmen") ?>
        </label>

        <label>
            <input type="hidden" name="modulecourse[evaluation_for_students]" value="0">
            <input type="checkbox" name="modulecourse[evaluation_for_students]" value="1"<?= $modulecourse['evaluation_for_students'] ? " checked" : "" ?>>
            <?= dgettext("lernmoduleplugin","Nutzer dürfen die Auswertung sehen") ?>
        </label>

        <label>
            <?= dgettext("lernmoduleplugin","Abspielen ab") ?>
            <input type="text" id="modulecourse_starttime" name="modulecourse[starttime]" value="<?= $modulecourse['starttime'] ? date("d.m.Y H:i", $modulecourse['starttime']) : "jederzeit" ?>"  data-datetime-picker>
        </label>

        <? if (class_exists('\\Grading\\Definition')) : ?>
            <? $gradebook_definitions = \Grading\Definition::findBySQL("course_id = ? ORDER BY name", array(Context::get()->id)) ?>
            <? if (count($gradebook_definitions)) : ?>
                <label>
                    <?= dgettext("lernmoduleplugin","Gradebook-Eintrag bei Erfolg setzen") ?>
                    <select name="modulecourse[gradebook_definition]">
                        <option></option>
                        <? foreach ($gradebook_definitions as $definition) : ?>
                        <option value="<?= htmlReady($definition->getId()) ?>"<?= $modulecourse['gradebook_definition'] == $definition->getId() ? " selected" : "" ?>>
                            <?= htmlReady($definition['name']) ?>
                        </option>
                        <? endforeach ?>
                    </select>
                </label>
                <label>
                    <input type="hidden" name="modulecourse[gradebook_rewrite]" value="0">
                    <input type="checkbox" name="modulecourse[gradebook_rewrite]" value="1"<?= $modulecourse['gradebook_rewrite'] ? " checked" : "" ?>>
                    <?= dgettext("lernmoduleplugin","Kann mehrmals absolviert werden.") ?>
                </label>
            <? endif ?>
        <? endif ?>

        <? if (!$module->isNew() && is_a($module, "CustomLernmodul")) : ?>
            <? $template = $module->getEditTemplate() ?>
            <? if ($template) : ?>
                <?= $template->render() ?>
            <? endif ?>
        <? endif ?>

    </fieldset>

    <div data-dialog-button>
        <?= \Studip\Button::create(dgettext("lernmoduleplugin","Speichern")) ?>
        <?= \Studip\Button::create(dgettext("lernmoduleplugin","Löschen"), "delete", array(
            'formaction' => PluginEngine::getLink($plugin, array(), "lernmodule/delete/".$module->getId()),
            'onClick' => "return window.confirm('".dgettext("lernmoduleplugin","Wirklich löschen?")."');"
        )) ?>
        <? if (!Request::isAjax()) : ?>
            <?= \Studip\LinkButton::create(dgettext("lernmoduleplugin","Abbrechen"), PluginEngine::getURL($plugin, array(), "lernmodule/overview")) ?>
        <? endif ?>
    </div>

</form>

<script>
    STUDIP.Lernmodule = {
        selectImage: function () {
            jQuery('#image_preview').css('background-image', 'url(' + jQuery(this).find(':selected').data('url') + ')');
        },
        selectNextImage: function () {
            var selected = jQuery('#select_image option:selected');
            selected.removeAttr('selected');
            if (selected.is(":last-of-type")) {
                var optgroup = selected.closest("optgroup");
                if ((optgroup.length === 0) || (optgroup.is(":last-of-type"))) {
                    jQuery('#select_image optgroup').first().find("option:first-of-type").attr('selected', 'selected');
                } else {
                    optgroup.next().find("option:first-of-type").attr('selected', 'selected');
                }
            } else {
                selected.next().attr('selected', 'selected');
            }
            jQuery('#select_image').trigger("change");
        },
        selectPreviousImage: function () {
            var selected = jQuery('#select_image option:selected');
            selected.removeAttr('selected');
            if (selected.is(":first-of-type")) {
                var optgroup = selected.closest("optgroup");
                if ((optgroup.length === 0) || (optgroup.is(":first-of-type"))) {
                    jQuery('#select_image optgroup').last().find("option:last-of-type").attr('selected', 'selected');
                } else {
                    optgroup.prev().find("option:last-of-type").attr('selected', 'selected');
                }
            } else {
                selected.prev().attr('selected', 'selected');
            }
            jQuery('#select_image').trigger("change");
        }
    };
</script>

<?

if (!$module->isNew()) {
    $actions = new ActionsWidget();
    $actions->addLink(
        dgettext("lernmoduleplugin","Lernmodul herunterladen"),
        PluginEngine::getURL($plugin, array(), "lernmodule/download/" . $module->getId()),
        Icon::create("download", "clickable")
    );
    Sidebar::Get()->addWidget($actions);
}

$views = new ViewsWidget();
$views->addLink(
    $module['name'],
    PluginEngine::getURL($plugin, array(), "lernmodule/view/".$module->getId()),
    null,
    array()
);
$views->addLink(
    dgettext("lernmoduleplugin","Auswertung"),
    PluginEngine::getURL($plugin, array(), "lernmodule/evaluation/".$module->getId()),
    null,
    array()
);

Sidebar::Get()->addWidget($views);
