![](resources/Logo.png)

Nin is a minimalistic PHP framework based on some of the ideas of Yii 1 and 2. It stands for "No It's Not", a play on Yii's "Yes It Is". Nin makes a few promises:

* **Every feature is optional.** You don't need to use views. You don't need to use models. You don't even need to use the controller or routing system.
* **Sensible configuration defaults.** As a result of the first promise, the entire configuration should make sense to allow for a minimalistic approach in the most common environments.
* **The API is straight forward.** Everything in the API should be self-explanatory and obvious.
* **Minimal amount of code required.** To work with the API, there shouldn't be too many lines of code. This helps the minimalism and simplicity of the system.

Anything that breaks these promises should be considered a bug.

# How does it work?
By relying on modern PHP features, we can achieve some of the effects of Yii 2 while keeping the integrity of some of Yii 1's well-designed features.

Nin can be used in various ways. One of those ways is via its MVC system, where `Model` and `Controller` are the key classes, and views are included PHP files.

# Getting started
Quickly get started with Nin by installing the dependency via Composer. You can find the package [on Packagist](https://packagist.org/packages/codecat/nin). Install it by running:

```
$ composer require codecat/nin
```

You can also just download a release from Github and include Nin from somewhere else if you prefer.

If you plan on using Nin's routing system, make sure you create the necessary `.htaccess` file:

```
$ cp vendor/codecat/nin/.htaccess .
```

Then create `index.php`:

```PHP
<?php
include('vendor/codecat/nin/nf.php');
nf_begin(__DIR__);
```

**Nginx note:** You do not have to copy the `.htaccess` file. Instead, copy the contents inside of `nginx.conf`, and paste them inside the `server { }` block.

**Docker note:** You don't need to have the `.htaccess` file, as this is handled automatically by the server config inside the docker image.

# The most minimalistic example
After calling `nf_begin`, you're all set. Since every feature is optional, you can make a page with only a single `index.php` file. For example, to display a list of posts from a table in a MySQL database, your php file could be as small as this:

```PHP
<?php
include('vendor/codecat/nin/nf.php');
nf_begin(__DIR__, array(
  'mysql' => array(
    'username' => 'root',
    'database' => 'nin'
  )
));

class Post extends Nin\Model {
  public static function tablename() { return 'posts'; }
}

foreach(Post::findAll() as $post) {
  echo '<b>' . Nin\Html::encode($post->User) . '</b>: ' . Nin\Html::encode($post->Message) . '<br>';
}
```

These model classes can also be located in separate files inside of a `models` folder, and they will be autoloaded when they're needed.

# Using controllers
You have again 2 choices for controllers; single file or multiple files.

To create controller classes, make a folder `controllers` in the same directory as the `index.php` is located. Inside of this folder, we can make a file `IndexController.php`:

```PHP
<?php
class IndexController extends Controller {
  public function actionIndex() {
    echo 'This is the index page!';
  }
}
```

By default, Nin's routing will use `index/index` as the standard route. Routing works as `controller/action`. This means that if you create a controller called `FooController` containing a function `actionBar()`, Nin will be able to instantiate a `FooController` and call `actionBar()` when a user visits `foo/bar`.

Note that the use of files for controllers again is optional. It is possible to have a single `index.php` file with controller classes defined inline. You will however need to call an extra function `nf_begin_routing()` for the actual routing to begin.

What follows is a very minimalistic page that supports an index page as well as a `foo/bar` route:

```PHP
<?php
include('vendor/codecat/nin/nf.php')
nf_begin(__DIR__);

class IndexController extends Nin\Controller {
  public function actionIndex() {
    echo 'This is the index!';
  }
}

class FooController extends Nin\Controller {
  public function actionBar() {
    echo 'This is Foo/Bar!';
  }
}

nf_begin_routing();
```

# Using views
You can also do the following inside of a controller's action function:

```PHP
$this->render('foo');
```

This will render the `foo` view, located at `views/controller/view.php`. So if the above line was in `IndexController`, the view would be located at `views/index/foo.php`.

You can also pass parameters to the `render()` function, like so:

```PHP
$this->render('foo', array(
  'bar' => 'hello ',
  'foobar' => 'world'
));
```

Your view can then use these parameters as if the keys in the array were PHP variables:

```
<p>The controller says: <b><?= $bar . $foobar ?></b></p>
```

If you create a layout file at `views/layout.php`, you can use that as a wrapper for your views. It will expose the `$content` variable for the rendered view. It could for example look like this:

```
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
FROM codecatt/nin:1.3
COPY . /var/www/html
```

And then in your `index.php` you include Nin like this:

```PHP
include('../nin/nf.php');
```

Note that you do not need to do anything in `.htaccess` to get the router to work, as this is automatically handled by the Docker image.

There are several tags available:

* `1.3`: The latest 1.3.x version.
* `1.2`: The latest 1.2.x version.
* `latest`: The current code available on the master branch.
