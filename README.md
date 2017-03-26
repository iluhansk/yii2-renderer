Yii2 renderer
=============
Provides visualization functionality (rendering views) to any component of yii2 application

* simple sintax
* view is searched by classes inheritance 

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist iluhansk/yii2-renderer "*"
```

or add

```
"iluhansk/yii2-renderer": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Add RenderBehavior to target class:

<pre>
namespace common\components;

use \yii\base\Component;
use iluhansk\renderer\RenderBehavior;

class Car extends Component {

    public function behaviors() {
        return [
            'renderer' => [
                'class' => RenderBehavior::className(),
                'baseClass' => __CLASS__,
                'baseViewDir' => '@frontend/views/car',
                'defaultView' => 'model',
                'viewNotFound' => '-',
            ]
        ];
    }

}
</pre>

And use rendering in an place:

<pre>
use common\components\Car;

$car = new Car();
echo $car->render(); //render default view (in this example it is "model")
echo $car->render('options',['data'=>'passed','to'=>'view']); //render view "options"
</pre>

Inside view you can access to object by var $context


Example of view search process:
-------------------------------

continue of example above:

<pre>
class Sedan extends Car {
...
}
class Econom extends Sedan {
...
}

$car = new Econom();
echo $car->render('options'); 
//First, a view "options" will be search at @frontend/views/car/sedan/econom directory
//If it is not found, then it will be search at @frontend/views/car/sedan directory
//If it is not found, then it will be search at @frontend/views/car (it from behavior field baseViewDir) 
//If it is not found, then it will return viewNotFound text (in this example it is '-')
</pre>
