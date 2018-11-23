# Piggly Autoloader

**Piggly Autoloader** is a simple PHP autoloader that loads classes files based in a list which contains:

1. The full namespace;
2. The diretory to namespace;
3. The file extensions to namespace.

## Brief Explanation

### What is Autoloading?

Class autoloading, typically referred to simply as *"autoloading"*, is a method of including necessary class files in a project dynamically at runtime as opposed to hard-coding an include statement for each class file dependency in every file. This allows for faster development and less bloated files.

### PHP support for Autoloading

The `spl_autoload_register()`function, used by our class, added in PHP 5.1.2 provided more flexibility and is considered to be the proper method of implementing autoloading in PHP today. More recently, PHP 5.3 has added support for namespaces.

### The Piggly Autoloader

We’ve created a class prepared to be cached and prevent to do a recursively scan in the path folder each time a new class is called.

## Purpose

The PSR-0/[PSR-4] are great. But there is some projects we want to do something more organizated or even more clearly to files auto express what they are. In **Studio Piggly** we like to do something like this:

```
src/
	app/
		controllers/
			controller.php
			base.controller.php
			user.controller.php
		models/
			model.php
			base.model.php
			user.model.php
```

By using a custom extension to each namespace, we can make it more clearly. To us achieve it, then we’ve created this `Autoload` class.

## Prerequisites

This project is aimed at programmers with a basi knowledge of **PHP**. It assumes that you already have:

1. A web server running **PHP 5.1.2** or more;
2. A basic understanding of general programming concepts and PHP syntax.

## Requirements

**PHP 5.3+** or minimal **PHP 5.1.2** version.

## How to use it

**Piggly Autoloader** loads all classes by using their namespace and a custom extension. To able it, this class maintains a log with all directories to the major namespaces prefixes.

It works as follows with the directories structure...

    ABSPATH
    	src/
    		app/
    			controllers/ 				# Package\App\Controllers
    				controller.php
    				base.controller.php
    				user.controller.php
    			models/ 					# Package\App\Models
    				model.php
    				base.model.php
    				user.model.php

...adds, for example, the *"Package\App\Models"* prefix with the *“app/models"* directory along with the file extensions *“model”*.

You can organize it, by using the extension you want before the *“.php”* extension. To keep it organized, you have to save the files as:

     {filename}.{extension}.php
     	main.controller.php
     	database.manager.php
     	book.model.php

But, when do you have a standard class which can be extended, you have to save as only using the extension name:

     {extension}.php
     	controller.php
     	manager.php
     	model.php

     ^ These are the standard objects.

### Step by Step

1. Create a new instance to the Autoload;
2. Add a namespace with addNamespace() function;
3. Register the autoload with register() function.

Getting our directory example, you do the following:

```php
$loader = new \Piggly\Autoload( 'Package', 'src' );

$loader->addNamespace( 'App\Controllers', 'app/controllers', 'controller' );
$loader->addNamespace( 'App\Models', 'app/models', 'model' );

$loader->register();
```

#### Creating a Cache

You can save the namespaces into a cache. With this, you prevent to mount the array of namespaces everytime:

```php
// Gets the array with all namespaces
$namespaces = $loader->getNamespaces();

// Recovers the array
$loader->setNamespaces( $namespaces );
```

#### Shortcuts

```php
// Auto sets the base dir and file extension
// the same as:
// $loader->addNamespace( 'App\Controllers', 'app/controllers', 'controllers' );
$loader->addNamespace( 'app\controllers' );

// Auto sets file extension
// the same as:
// $loader->addNamespace( 'App\Controllers', 'app/controllers', 'controllers' );
$loader->addNamespace( 'app\controllers', 'app/controllers' );

// Auto sets the base dir but with a custom file extension
// the same as:
// $loader->addNamespace( 'App\Controllers', 'app/controllers', 'controller' );
$loader->addNamespace( 'app\controllers', null, 'controller' );
```

## Keep it in mind

*This class was created to organize the directories and files by a custom extensions. With you don't agree with this, it's not for you.*

## Changelog

**v1.0.0**

* Initial release;

## License

Copyright (C) 2018
​        Caique Monteiro Araujo <caique@studiopiggly.com.br>
​        Piggly Dev <dev@piggly.com.br>

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <https://www.gnu.org/licenses/>.

## Contributing

Suggestions and pull requests are always welcome.