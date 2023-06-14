<p align="center">
  <img src="https://github.com/codecat/Nin/raw/master/resources/Logo.png">
</p>

Nin is a minimalistic PHP framework originally based on some of the ideas of Yii. It stands for "No It's Not", a play on Yii's "Yes It Is".

# How does it work?
Nin is an MVC system, where `Nin\Model` and `Nin\Controller` are the key classes, and views are included PHP files.

# Getting started
Quickly get started with Nin by installing the dependency via Composer. You can find the package [on Packagist](https://packagist.org/packages/codecat/nin). Install it by running:

```
$ composer require codecat/nin
```

Then create `index.php`:

```php
include 'vendor/autoload.php';
nf_begin();
```

## Detailed installation
There is also a handy Docker image you can use which has the webserver and PHP preconfigured. Scroll to the bottom of the readme to learn more.

You can also download a release from Github (or the master branch) and include Nin from somewhere else if you prefer. Depending on your webserver, you might also need to configure a rewrite rule so everything points to `index.php`. Example configurations and basic instructions for popular webservers can be found in [`server-configs`](server-configs). I personally recommend [Caddy](https://caddyserver.com/) with php-fpm, since that works very well and already has proper routing to `index.php` by default.

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

# Using a database
Nin supports PostgreSQL, MySQL, and SQLite. To begin using a database such as PostgreSQL with Nin, specify the database connection information as a parameter to `nf_begin`:

```php
nf_begin([
  'postgres' => [
    'hostname' => 'localhost',
    'password' => 'password',
  ],
]);
```

Once this configuration is set, you are ready to create and use models.

Note that the `postgres` key above is a shortcut for the more verbose database configuration:

```php
nf_begin([
  'db' => [
    'class' => 'Postgres',
    'options' => [
      'hostname' => 'localhost',
      'password' => 'password',
    ],
  ],
]);
```

The more verbose configuration allows you to implement other third party database contexts and query builders if needed.

# Using models
Models are defined as classes. Static functions are used to configure how the model behaves. For example, a user model looks like this:

```php
class User extends \Nin\Model {
  public static function tablename() { return 'users'; }
}
```

By default, the primary key of models is defined as `ID`, but can be changed by defining a static function `primarykey`:

```php
public static function primarykey() { return 'id'; }
```

This tells Nin that the database table containing rows of that model is called `users`.

You can now use the new `User` class to do all kinds of operations. To create a new user:

```php
$user = new User();
$user->Username = 'admin';
$user->Password = 'hunter2';
$user->Age = 24;
$user->save();

// Nin will automatically set the ID of the model after inserting it
echo 'New user ID: ' . $user->ID;
```

(Sidenote: Make sure you strongly hash passwords in your database, the example above is only for demonstration purposes!)

To find a user by ID:

```php
$user = User::findByPk(1);
if (!$user) {
  die('No user found!');
}
echo 'Hello, ' . $user->Username;
```

To find a user by attributes (column values):

```php
$user = User::findByAttributes([ 'Username' => 'admin' ]);
if (!$user) {
  die('No user found!');
}
echo 'Hello, ' . $user->Username;
```

You may also use `findAll` and `findAllByAttributes` to get multiple models. They return arrays and work how you would expect:
```php
$users = User::findAll();
$users = User::findAllByAttributes([ 'Age' => 24 ]);
```

You can optionally pass options to `findAll` and `findAllByAttributes` as an array in the second parameter. The accepted keys are:

* `group` Perform a group by query with its value
* `order` The order to sort the objects in (`ASC` or `DESC`, by default this is `ASC`)
* `orderby` Which column to sort the object by (by default this is the primary key)
* `limit` Either an integer of how many items you want to get at most, or an array for a range of items

Models can also have **relational** properties. For example, if a user can have multiple posts, you would define the `User` class like this:

```php
class User extends \Nin\Model {
  public static function tablename() { return 'users'; }

  public function relations() {
    return [
      'posts' => [ HAS_MANY, 'Post', 'Author' ],
    ];
  }
}
```

You can then simply use `$user->posts` to get an object array of the model `Post`, using the post column `Author`. There are 3 types of relationships you can define:

* `BELONGS_TO` finds one object using **their classname** and **my column**
* `HAS_MANY` finds multiple objects using **their classname** and **their column**
* `HAS_ONE` finds one object using **their classname** and **their column**

# Middleware
You can set up middleware for specific routes. To do this, use the functions `nf_route_middleware_begin` and `nf_route_middleware_end` to wrap your `nf_route` calls in. For example, to require a user to be logged in for certain routes, you can do this:

```php
nf_route_middleware_begin(new Nin\Middleware\AuthSession());
nf_route('/posts', 'PostsController.Index');
nf_route('/users', 'UsersController.Index');
nf_route_middleware_end();
```

When someone navigates to `/posts` or `/users` and they are not logged in (through the user session management provided through `Nin\Nin::setuid()`), the request will stop and never make it to the controller. Additionally, by default `AuthSession` will redirect the user to `/login` as well, so the user can log in.

Middleware can be stacked by doing multiple calls to `nf_route_middleware_begin`. Note that you must also call `nf_route_middleware_end` the same amount of times, or Nin will throw errors.

# Docker
Nin is also available as [a docker image](https://hub.docker.com/r/codecatt/nin) based on `caddy:alpine`. Here's a quick example on how to use Nin in your Dockerfile:

```
FROM codecatt/nin:2.0
COPY . /var/www/html
```

And then in your `index.php` you include Nin like this:

```php
include('../nin/nf.php');
```

Note that you do not need to do any manual configuration, as this is automatically handled by the Docker image.

There are several tags available:

* `latest`: The latest version.
* `2.0`: The latest 2.0.x version.
* `1.6`: The latest 1.6.x version.
* `1.3`: The latest 1.3.x version.
* `1.2`: The latest 1.2.x version.
