 <p align="center"><img src="assets/img/logo.png" width="600"></p>
 
 <p align="center">	
  	 Woski is a simple fast PHP framework for the Realm
    <br />
    <a href="https://docs.woski.xyz" target="_blank"><strong>Explore the docs</strong></a>    <br />
    <br />
  </p>
</p>


<br/><br/>

### Installation

Clone the repository

```shell
$ git clone git@github.com:clintonnzedimma/woski.git
```





### Running your application through Woski CLI
CD into your projects directory and run your application using the command below

```shell
$ php woski --run --port 3030
```

Now you open [http://localhost:3030](http://localhost:3030) in your browser to see your application.

### Your first hello world.
Open your `index.php` file, and add a new route

```php
$app->get('/hello', function ($req, $res) {
    return 'Hello Realm';
});
```

Visit [http://localhost:3030/hello](http://localhost:3030/hello). You're done.
