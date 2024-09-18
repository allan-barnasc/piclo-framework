<?php 

namespace Structure\Classes;

class View
{
    private array $data;
    private string $document;

    /**
     * Structure of methods in template engine
     * 
     * @define template = ?template("path/to/file") -> Method that define the template view
     * @define yield = ?yield("content") -> Method that define the content inside the template
     * @define inside = ?inside("content") -> Method that push in ?yield the content that'll be viewed
     * 
     * @var const METHODS_REGEX
     */
    private const METHODS_REGEX = [
        'template' => '/(?<=\?template\(")(.*\n?)(?="\))/',
        'yield' => '/(?<=\?yield\(")(.*\n?)(?="\))/',
        'inside' => '/(?<=\?inside\("{yield}"\))(.*\n?)(?=\?endinside)/',
    ];

    /**
     * Initial setup
     * 
     * @param string $path The location of view
     * @param array $data The values that'll be passed to view
     * 
     * @return void
     */
    public function __construct(string $path, array $data = [])
    {
        $this->data = $data;
        $this->document = file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/views/{$path}.php");
    }

    /**
     * Method that mounts the document view that'll be rendering
     * 
     * @return void
     */
    public function mount(): void
    {
        extract($this->data);

        $template = $this->getTemplate();

        if ($template) {
            $yields = $this->getYieldsDefinitions($template);

            $this->setInsiders($template, $yields);

            $this->document = $template;
        }

        ob_start();
        eval('?>'.$this->document);
        $final = ob_get_clean();

        echo $final;
    }

    /**
     * Method that'll return the Template structure if exists or null
     * 
     * @return string|null
     */
    private function getTemplate(): ?string
    {
        $template_dir = $this->getTemplateDirectory();

        if (!$template_dir)
            return null;

        return file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/views/{$template_dir}.php");
    }

    /**
     * Method that'll return the Template directory if exists or null
     * 
     * @return string|null
     */
    private function getTemplateDirectory(): ?string
    {
        $matches = [];
        preg_match(Self::METHODS_REGEX['template'], $this->document, $matches);

        return $matches[0] ?? null;
    }

    /**
     * Method that'll return the Yields definition inside a exist template
     * 
     * @param string $template
     * 
     * @return array|null
     */
    private function getYieldsDefinitions(string $template): ?array
    {
        $matches = [];
        preg_match_all(Self::METHODS_REGEX['yield'], $template, $matches);

        return $matches[0] ?? null;
    }

    /**
     * Method that'll return the contets inside document by yield definitions
     * 
     * @param string $yield
     * 
     * @return array|null
     */
    private function getInsidersContent(string $yield): ?array
    {
        $matches = [];
        $trimDocument = trim(preg_replace('/\s\s+/', ' ', $this->document));

        preg_match_all(str_replace('{yield}', $yield, Self::METHODS_REGEX['inside']), $trimDocument, $matches);

        return $matches[0] ?? null;
    }

    /**
     * Method that'll fill the yields definitions inside the template
     * 
     * @param string $template
     * @param array $yields
     * 
     * @return void
     */
    private function setInsiders(string &$template, array $yields): void
    {
        foreach ($yields as $yield) {
            $insidersContent = $this->getInsidersContent($yield);

            foreach ($insidersContent as $inside) {
                $template = str_replace('?yield("'.$yield.'")', $inside, $template);
            }
        }
    }
}