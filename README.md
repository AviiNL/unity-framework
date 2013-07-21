unity-framework
===============

Unity is a fully stacked modular framework in which the way you build your applications is completely up to you. 
You'll simply create a set of objects which you register in your application and let the framework handle the more 
boring stuff like dependency resolving, dynamic argument injection and routing.

Installation
------------

Simply install via composer:

    "require" : {
      "php" : ">=5.4.3",
      "unity/framework-package": "dev-master"
    }
  
  
A simple example to get things rolling
--------------------------------------

After installation, create an application directory wherever you want on your webserver 
(preferably not accessible from the browser). For this example we're going with the directory `/app`. The
public directory will be `htdocs`.

So your webserver directory structure would pretty much look like this:

* `/app` will be the directory we'll be working in.
* `/htdocs` is accessible from the browser.
* `/vendor` is the composer generated vendor directory.

Do note that **all** directories and structure are up to you to decide, this is just an example.




Let's start by creating `index.php` in the `htdocs` directory, because when this file is made, it's not likely it'll
ever be changed again. This will simply load the required files and instantiate our first application.
 
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/application.php;
    
$debug_mode = true;
$app = new Application($debug_mode);
$app->getService('dispatcher')->dispatchFromRequest();
```

`application.php` will consist of a simple class extending on `Unity\Framework`.

```php
<?php
class Application extends Unity\Framework
{
    public function registry()
    {
        return array(
            new myFirstService(),
            new MyFirstController()
        );
    }
}
```

As you can see, the **registry** method simply returns an array of newly created objects. For this example, we're using
**MyFirstController** and **MyFirstService**.

**NOTE: ** [this structure is subject to change to be a bit more intuitive]; Bundle-support has already been added but
it's use isn't mandatory. For now, we'll move on with the example.

The file `MyFirstController` can be placed anywhere, but for now let's stick with the `app` directory. So, for this we'll
create a new file called `/app/MyFirstController.php`. Make sure that the file is included or update your composer.json
file to also include the `app` directory in its autoloading.

```php
<?php
use Unity\Component\Controller\BasicController
use Unity\Component\HTTP\Route;
    
class MyFirstController extends BasicControler
{
    /**
     * @Route("/hello/{name}")
     */
    public function hello($name, MyFirstService $svc)
    {
        echo $svc->greet($name);
    }
}
```

Movin on to the service, `app/MyFirstService.php`:

```php
<?php
use Unity\Component\Service\Service;
    
class MyFirstService extends Service
{
    public function __construct()
    {
        // Specify a name (classified as "slug")
        $this->setName('my-first-service');
    }
    
    public function greet($name)
    {
        return sprintf('<h1>Hello, %s.<h1>', $name);
    }
}


// url: /hello/Jenny
// output: Hello, Jenny.
```

So, what happens here is that when you open `http://localhost/hello/Jenny', the method *hello* we've just written will be
executed because it matches the specified route. Also, because we've created a Service, you can simply request for it
by typehinting the classname in the arguments for the method, and it'll be there!

You can also specify dependencies on other services by using the `addDependency` method in the service constructor. If your
service requires another server, you'll need to create a `configure` method for your servic as well. The configure 
method will act as a "constructor" and will have your dependencies injected in it's arguments automatically. All you have
to do is typehint the class names of the dependencies.

