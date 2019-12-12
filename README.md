IBoo Backend Challenge
==========

_Administration panel to manage workers, machines and pieces_

## Starting 🚀

_These instructions will allow you to obtain a copy of the project on your local machine for development and testing purposes._

### Pre-requirements 📋

```
php: ^7.0
```

## Running the tests ⚙️

First step:
 
Register 2-3 workers (username, email, password)

Note: by default all users are created with the role ROLE_USER.

Second step:

Access the database and modify the role field of a user. Change ROLE_USER to ROLE_ADMIN.

Third step:

Login with credentials

Note: 
You can check that when you access the list of workers, if you access as ROLE_ADMIN, you can see all the workers, all the machines and all the parts, however if you access as ROLE_USER you only see yourself, your machines and your pieces


## Built With 🛠️

* [Symfony](https://symfony.com/doc/current/index.html#gsc.tab=0)
* [Query-Builder](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/query-builder.html)
* [Twig](https://twig.symfony.com/doc/3.x/)
* [PhpStorm](https://www.jetbrains.com/phpstorm/)

## Authors ✒️

* **Salvador Florit Guinovart** - [Git](https://github.com/aereoh)