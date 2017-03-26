<?php

namespace iluhansk\renderer;

use Yii;
use yii\helpers\FileHelper;

/**
 * Class provides rendering functionality to any Component of application
 *
 * @author Ilya Perfilyev <ilya_perfi@mail.ru>
 */
class RenderBehavior extends \yii\base\Behavior {

    /**
     * @var string View used by default
     */
    public $defaultView;
    
    /**
     * @var string Text returned, if view file not found
     */
    public $viewNotFound = '';

    /**
     * @var string top class of rendering objects tree
     */
    public $baseClass;
    
    /**
     * @var string dirname containig views of top class (see @var baseClass)
     */
    public $baseViewDir;
    
    /**
     * @var callable Function/Method that return subDirectory for class that contains views
     */
    public $subdirCallback;

    protected $pathSeparator = '/';
    
    /**
     * return rendered view
     * @param string $view view name
     * @param [] $params parameters
     * @return string
     */
    public function render($view = null, $params = []) {
        if(empty($view)) {
            $view = $this->defaultView;
        }
        if (!empty($view) && $viewFile = $this->getViewFile($view)) {
            return Yii::$app->view->renderFile($viewFile, $params, $this->owner);
        }
        return $this->viewNotFound;
    }

    /**
     * Search a view file in different places by owner object class tree
     * @param string $view
     * @param string $class
     * @return string|false founded view file (absolute) OR false if not
     */
    protected function getViewFile($view, $class=null) {
        $class = $class ? $class : get_class($this->owner);
        $dir = Yii::getAlias($this->getViewPath($class));
        $viewFile = FileHelper::normalizePath($dir . $this->pathSeparator . $view);
        if($file = $this->checkViewFile($viewFile)) {
            return $file;
        }
        $parent = get_parent_class($class);
        if ($parent && $class != $this->baseClass) {
            return self::getViewFile($view, $parent);
        } else {
            return false;
        }
    }

    /**
     * check given $viewFile.
     * @param string $viewFile view file (absolute OR alias)
     * @return string|false view file (absolute) if exists OR false if not found
     */
    protected function checkViewFile($viewFile) {
        $path = $file = Yii::getAlias($viewFile);
        if (pathinfo($file, PATHINFO_EXTENSION) === '') {
            $path = $file . '.' . Yii::$app->view->defaultExtension;
            if (Yii::$app->view->defaultExtension !== 'php' && !is_file($path)) {
                $path = $file . '.php';
            }
        }
        return is_file($path) ? $path : false;
    }

    /**
     * Get the view dir of given class
     * @param type $class
     * @return type
     */
    protected function getViewPath($class = null) {
        $class = $class ? $class : get_class($this->owner);
        if($class != $this->baseClass) {
            if($parent = get_parent_class($class)) {
                $path = self::getViewPath($parent) . $this->pathSeparator . $this->getViewSubdir($class);
                return $path;
            }
        }
        $dir = $this->getBaseViewDir();
        return $dir;
    }
    
    protected function getBaseViewDir() {
        return is_array($this->baseViewDir) ? implode($this->pathSeparator, $this->baseViewDir) : $this->baseViewDir;
    }

    /**
     * return view subdirectory of given class
     * @param type $class
     * @return type
     */
    protected function getViewSubdir($class) {
        $short = (new \ReflectionClass($class))->getShortName();
        return is_callable($this->subdirCallback) ? call_user_func($this->subdirCallback, $short) : mb_strtolower($short);
    }
    
    
}
