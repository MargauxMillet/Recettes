<?php 

namespace App;

use AltoRouter;

class Router {

    private $pagePath;
    private $router;
    private $layout = "layout/default";

    public function __construct (string $pagePath)
    {
        $this->pagePath = $pagePath;
        $this->router = new AltoRouter();
    }

    public function get (string $url, string $targetfile, ?string $name = null): self
    {
        $this->router->map('GET', $url, $targetfile, $name);
        return $this;
    }

    public function post (string $url, string $targetfile, ?string $name = null): self
    {
        $this->router->map('POST', $url, $targetfile, $name);
        return $this;
    }

    public function getpost (string $url, string $targetfile, ?string $name = null): self
    {
        $this->router->map('GET | POST', $url, $targetfile, $name);
        return $this;
    }

    public function run (): self
    {
        $match = $this->router->match();
        $params = $match['params'];
        $targetfile = $match['target'];
        ob_start();
        require $this->pagePath . $targetfile . '.php';
        $content = ob_get_clean();
        require $this->pagePath . $this->layout . '.php';

        return $this;
    }

}

?>