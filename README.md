php-prototype
=============
A php class try to support javascript prototype function call.
```PHP
<?php
    require_once('Prototype.php');
    
    $a = new Prototype();
    
    // set variable.
    $a->set('test', 1);
    echo($a->get('test') . "\n");
    // print 1
    
    // enable schema
    $a->setSchemaEnabled(true);
    $a->setSchema('test', 'string');
    // $a->set('test', 2);
    // print Error: Variable('test') format is wrong.
    
    // dynamic add function
    $a->setFunc('funcTest', function($arg) {
        return $arg + 1;
    });
    echo($a->funcTest(3) . "\n");
    // print 4
    
    // dynamic add function with caller($that), function name have to add '_' underline.
    $a->setFunc('_selfTest', function($that, $str) {
        $that->set('test', $str);
    });
    $a->selfTest('selftest');
    echo($a->get('test') . "\n");
    // print selftest
    
    $b = new Prototype();
    $b->prototype = $a;
    var_dump($b->get('test'));
    // print NULL
    
    $b->selfTest('btest');
    echo($b->get('test') . "\n");
    // print btest
    
    $mongo = new MongoClient();
    $b->prototype = $mongo;
    // var_dump($b->connect());
    // equal to $mongo->connect()
?>
```
A module system by using Prototype.
```php
<?php
    require_once('Prototype.php');
    
    $a = new Prototype();
    $b = new Prototype();
    $b->setFunc('_testFunc', function($that, $val) {
        $that->set('test', $val);
    });
    $b->testFunc('123');
    var_dump($b->get('test'));
    // print string(3) "123"
    
    $a->setModule('mod', $b);
    $a->mod->testFunc('234');
    var_dump($a->mod->get('test'));
    // print string(3) "234"
    
    $a->hoist('mod', 'testFunc');
    $a->testFunc('345');
    var_dump($a->get('test'));
    // print NULL
    var_dump($b->get('test'));
    // print string(3) "345"
    
    $a->removeFunc('testFunc');
    $a->hoist('mod', '_testFunc');
    $a->testFunc('456');
    var_dump($a->get('test'));
    // print string(3) "456"
?>
```
A hybrid usage.
```php
<?php
    require_once('Prototype.php');
    
    $a = new Prototype();
    $b = new Prototype();
    $a->setModule('mod', $b);
    $c = new Prototype();
    $c->setFunc('_modProFunc', function($that, $val) {
        $that->set('test', $val);
    });
    $b->prototype = $c;
    
    $a->hoist('mod', 'modProFunc');
    $a->modProFunc('12345');
    var_dump($a->get('test'));
    // print NULL
    var_dump($a->mod->get('test'));
    // print string(5) "12345"
    
    $a->removeFunc('modProFunc');
    $a->hoist('mod', '_modProFunc');
    $a->modProFunc('23456');
    var_dump($a->get('test'));
    // print string(5) "23456"
?>
```
