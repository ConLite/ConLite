<?php

namespace ConLite\System\Gui;

class Page
{
    protected \Template $pageTemplate;
    protected \Template $contentTemplate;
    protected string $pageBase;

    public function __construct(
        protected string $pageName,
        protected string $pluginName = '',
        protected string $subMenu = '',
    )
    {
        $this->pageTemplate = new \Template();
        $this->contentTemplate = new \Template();
        $this->currentUser = \cRegistry::getCurrentUser();

        $this->setPageBase();
    }

    public function render($template = null, bool $return = false)
    {
        if ($template == NULL) {
            $template = $this->contentTemplate;
        }

        $this->renderMetaTags();
        $this->renderScripts();
        $this->renderStyles();

        $this->pageTemplate->set('s', 'PAGECLASS', implode(' ', $this->_bodyClassNames));

        return $this->pageTemplate->generate($this->pageBase, $return);
    }

    public function setPageBase(string $file = ''): void
    {
        if (empty($file)) {
            $this->pageBase = \cRegistry::getConfigValue('path', 'templates')
                . \cRegistry::getConfigValue('templates', 'generic_page');
        } else {
            if (\cFileHandler::readable($file)) {
                $this->pageBase = $file;
            }
        }
    }

    protected function renderMetaTags()
    {
    }

    protected function renderScripts()
    {
    }

    protected function renderStyles()
    {
    }
}