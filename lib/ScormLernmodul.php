<?php

class ScormLernmodul extends Lernmodul implements CustomLernmodul
{
    static public function detect($path)
    {
        return true;
    }

    public function afterInstall()
    {

    }

    public function getEditTemplate() {}

    public function getViewerTemplate($attempt, $game_attendance = null)
    {
        PageLayout::postMessage(MessageBox::info(dgettext("lernmoduleplugin","Dies ist ein SCORM-Modul, das noch nicht unterstützt wird.")));

        $templatefactory = new Flexi_TemplateFactory(__DIR__."/../views");
        $template = $templatefactory->open("scorm/view.php");
        $template->set_attribute("module", $this);
        $template->set_attribute("attempt", $attempt);
        return $template;
    }

    public function getEvaluationTemplate($course_id) {
        return null;
    }

    public function evaluateAttempt($attempt) {
        return null;
    }
}
