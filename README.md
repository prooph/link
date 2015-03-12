prooph LINK
===========

Prooph LINK is a data linking and workflow processing application. It is build on top of [prooph processing](https://github.com/prooph/processing)
and it is 100% open source. We make use of carefully selected open source libraries from the PHP and JS universe,
add specific logic for process automation and monitoring and put a modern UI on top of it to make
it available for all kind of users no matter if they have development experiences or not.

# Toolkit, Application, Both?

When you have a look at the source you will recognize that this repo is more or less empty. So what are we talking about?
prooph LINK is the name of an application but it consists of different modules that you can mix and match together with your own stuff.
This repo acts as a starting point. Think of it as a skeleton. You can download it, follow the installation instruction below and
you will get a fully running application. When you are familiar with it you probably will start to think about customization.
We can tell you that when you reach this point everything is already prepared for you, so you can start to individualize the
application.

# Installation

1. Git clone the project in the document root of your webserver. The application is based on Zend Framework 2.
You can follow the instructions of the [ZF2 SkeletonApplication](https://github.com/zendframework/ZendSkeletonApplication#web-server-setup) to get your web server up and running.

2. The web server needs write access to the directories: `config/autolaod` and `data`.

3. Run `php bin/install.php` from the root directory.

4. Navigate to prooph LINK in your browser of choice. The app detects the fresh installation and suggests the next steps.

# Work In Progress

Prooph LINK is not stable yet. However, we work hard on getting the job done. So it is just a matter of time.
The application runs already in production, but only with our support. Please contact us if you want to use it, too!

# Documentation

More information is coming soon. Stay tuned!

# Support

- Ask any questions on [prooph-users](https://groups.google.com/forum/?hl=de#!forum/prooph) google group.
- File issues at [https://github.com/prooph/link/issues](https://github.com/prooph/link/issues).

# Contribution

You wanna help us? Great!
We appreciate any help, be it on implementation level, UI improvements, testing, donation or simply trying out the system and give us feedback.
Just leave us a note in our google group linked above and we can discuss further steps.

Thanks,
your prooph team