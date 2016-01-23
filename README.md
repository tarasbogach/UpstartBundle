#UpstartBundle
##About
This is [Symfony](http://symfony.com/what-is-symfony) bundle for painless [Upstart](http://upstart.ubuntu.com/cookbook/#introduction) configuration.
It helps to make any symfony command (or any other script) run forever in background and restart on fails.
Most common example of such script is [queue](https://www.rabbitmq.com) [consumer](https://github.com/videlalvaro/rabbitmqbundle), another example is [websocket](http://socketo.me) server.
##Installation
Require the bundle and its dependencies with composer:
```bash
$ composer require sfnix/upstart
```
Register the bundle:
```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        new SfNix\UpstartBundle\SfNixUpstartBundle(),
    );
}
```
##Usage
Add the `upstart` section to your configuration file:
```yml
# app/config/upstart.yml
upstart:
    project: imaging
    default:
        verbose: 1
        native:
            setuid: "www-data"
    job:
        websocket:
            script: "php bin/chat-server.php"
        imageResizer:
            quantity: 10
            command: "rabbitmq:consumer image.resize -w"
        faceRecognizer:
            quantity: 5
            script: "python faceRecognizer.py"
            native: {killSignal: "SIGKILL"}
```
TODO
##Full configuration reference
TODO