prooph LINK
===========

![flowchart](https://github.com/prooph/link-process-manager/blob/master/docs/img/flowchart.png)

Prooph LINK is a workflow processing application written in PHP. It is designed to run in standard PHP environments be it shared hosting or a managed PHP infrastructure. The modular architecture allows easy integration of any software that offers a public accessible API. Distributed Domain-Driven Design, CQRS and Event Sourcing quarantee a scalable and maintainable software. The team of prooph software GmbH provides long term support for prooph LINK. You can try it without any registration or contract. It is open source!

If you need help or more information get in touch with us. We look forward to hear from you. 

# Toolkit, Application, Both?

When you have a look at the source you will recognize that this repo is more or less empty. So what are we talking about?
This repo acts as a starting point. Think of it as a skeleton. You can download it, follow the installation instruction below and
you will get a fully running application. When you are familiar with it you probably will start to think about customization.
We can tell you that when you reach this point everything is already prepared for you, so you can start to individualize the application and include it in your own project.
We make use of the module system of the Zend Framework 2 and use composer to pull in prooph LINK modules. This way you can decide of your own which modules you want to use and of course you can mix and match them with your own modules.

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

Please referrer to the module documentations. You can find all modules included in the default skeleton in the list below.

- [Prooph\Link\Application](https://github.com/prooph/link-app-core)
- [Prooph\Link\Dashboard](https://github.com/prooph/link-dashboard)
- [Prooph\Link\ProcessManager](https://github.com/prooph/link-process-manager)
- [Prooph\Link\ProcessorProxy](https://github.com/prooph/link-processor-proxy)
- [Prooph\Link\FileConnector](https://github.com/prooph/link-file-connector)
- [Prooph\Link\SqlConnector](https://github.com/prooph/link-sql-connector)
- [Prooph\Link\MessageQueue](https://github.com/prooph/link-message-queue)

# Support

- Ask any questions on [prooph-users](https://groups.google.com/forum/?hl=de#!forum/prooph) google group.
- File issues at [https://github.com/prooph/link/issues](https://github.com/prooph/link/issues).

# Contribution

You wanna help us? Great!
We appreciate any help, be it on implementation level, UI improvements, testing, donation or simply trying out the system and give us feedback.
Just leave us a note in our google group linked above and we can discuss further steps.

Thanks,
your prooph team
