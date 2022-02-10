![](resources/Logo.png)

Nin is a minimalistic PHP framework originally based on some of the ideas of Yii. It stands for "No It's Not", a play on Yii's "Yes It Is".

# How does it work?
Nin is an MVC system, where `Nin\Model` and `Nin\Controller` are the key classes, and views are included PHP files.

# Getting started
Quickly get started with Nin by installing the dependency via Composer. You can find the package [on Packagist](https://packagist.org/packages/codecat/nin). Install it by running:

```
$ composer require codecat/nin
```

You can also just download a release from Github and include Nin from somewhere else if you prefer.

Make sure you create the necessary `.htaccess` file to make sure the routing works:

```
$ cp vendor/codecat/nin/.htaccess .
```

Then create `index.php`:

```php
include('vendor/codecat/nin/nf.php');
nf_begin(__DIR__);
```

**Nginx note:** You do not have to copy the `.htaccess` file. Instead, copy the contents inside of `nginx.conf`, and paste them inside the `server { }` block.

**Docker note:** You don't need to have the `.htaccess` file, as this is handled automatically by the server config inside the docker image.

# The most minimalistic example
After calling `nf_begin`, you automatically have 1 route: `/`. By default, it points to `IndexController.Index`. This means it will look for a class called `IndexController` and a method of that class called `actionIndex`.

To create controller classes, make a folder `controllers` in the same directory as the `index.php` is located. Inside of this folder, we can make a file `IndexController.php`:

```php
class IndexController extends \Nin\Controller {
  public function actionIndex() {
    echo 'This is the index page!';
  }
}
```

Routing works as `controller.action`, where `controller` is a class name, and `action` is the action name corresponding to a method in the class prefixed with `action`. For example, `FooController.Bar` will instantiate `FooController` and call `actionBar` on it.

Routes are defined using the `nf_route` function:

```php
nf_route('/', 'IndexController.Home');
nf_route('/info', 'IndexController.Info');
nf_route('/user/:username', 'UserController.Profile');
```

The last `nf_route` call in the example above has a special parameter in its path, `:username`. This will become a parameter in your action method. So in the above example, the third route would use this controller class:

```php
class UserController extends \Nin\Controller {
  public function actionProfile(string $username) {
    // Do something with $username
  }
}
```

Note that you can specify a type for the method parameter as well, to automatically convert to the correct type. For example, `int $id` will ensure you're definitely getting an integer for a parameter.

Action method parameters can also be set using URL parameters fetched from `$_GET`. For example, if your route was defined as simply `/user`, then you can still set `string $username` by making the URL `?username=foo`. When `username` is not provided in the URL, Nin will throw an error about a missing required parameter. You can make the parameter optional by giving the method parameter a default value, for example: `string $username = 'admin'`.

Parameters will also be passed to the controller constructor, if it accepts parameters. They behave exactly like method parameters. For example, if you have a route `/user/:id/posts` pointing to `UserController.Posts`, you can use the following controller:

```php
class UserController extends \Nin\Controller {
  private $user;

  public function __construct(int $id) {
    $this->user = User::findByPk($id);
  }

  public function actionPosts() {
    // Do something with $this->user
  }
}
```

# Using views
You can also do the following inside of a controller's action method:

```php
$this->render('foo');
```

This will render the `foo` view, located at `views/controller/view.php`. So if the above line was in `IndexController`, the view would be located at `views/index/foo.php`.

You can also pass parameters to the `render()` function, like so:

```php
$this->render('foo', [
  'bar' => 'hello ',
  'foobar' => 'world'
]);
```

Your view can then use these parameters as if the keys in the array were PHP variables:

```
<p>The controller says: <b><?= $bar . $foobar ?></b></p>
```

If you create a layout file at `views/layout.php`, you can use that as a wrapper for your views. It will expose the `$content` variable for the rendered view. It could for example look like this:

```html
<!doctype html>
<html>
  <head>
    <title>My website</title>
  </head>

  <body>
    <h1>My website</h1>
    <div class="content">
      <?= $content ?>
    </div>
  </body>
</html>
```

# Docker
Nin is also available as [a docker image](https://hub.docker.com/r/codecatt/nin). Here's a quick example on how to use Nin in your Dockerfile:

```
FROM codecatt/nin:2.0
COPY . /var/www/html
```

And then in your `index.php` you include Nin like this:

```php
include('../nin/nf.php');
```

Note that you do not need to do anything in `.htaccess` to get the router to work, as this is automatically handled by the Docker image.

There are several tags available:

* `latest`: The latest version.
* `2.0`: The latest 2.0.x version.
* `1.3`: The latest 1.3.x version.
* `1.2`: The latest 1.2.x version.
