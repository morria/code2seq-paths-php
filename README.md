# PHP Code2Seq Paths

Read PHP source code and produce a file suitable for use by [code2seq](http://code2seq.org)
for training and evaluating a model.

## Usage

To build the package just run `composer install`. Make sure you have PHP installed along with
the [php-ast](https://github.com/nikic/php-ast) extension. You can also use the Docker
container with all dependencies defined in `.devcontainer`.

Once built you can run the tool via

```
./bin/code2seq-paths path/to/php/dir > method_contexts.c2s
```

and from here you can use the file `method_contexts.c2s` as input to [code2seq](https://github.com/tech-srl/code2seq).
Keep in mind that the file produced here will need to be preprocessed within `code2seq`.

**TODO**: describe how to do that.


## Flags

You can see the available flags via `./bin/code2seq-paths --help`.

```
Usage: ./bin/code2seq-paths [options] [files...]]
 -h, --help
 Get this help information

 -s SEED, --seed SEED
 The seed to use before shuffling the target contexts.

 -l LENGTH, --max-length=LENGTH
 The maximum length of a target context. Defaults to unbounded.

 -i, --ids
 Use IDs rather than names for context nodes. Defaults to false.

 ...
 All other options will be treated as file names to
 read as PHP source.
```

## Docker

To avoid setting up a proper environment, you can run `code2seq-paths-php` from
a Docker image as follows.

```
docker pull morria/code2seq-paths-php
docker run --name code2seq-paths-php --volume PATH_TO_SOURCE:/workspace morria/code2seq-paths-php /workspace/
```

To build the container from source run the following.

```
composer install
docker build -t morria/code2seq-paths-php .
```

## Output Format

The output is structured as follows.

```
LINE := FUNCTION_NAME WS CONTEXT_LIST;

FUNCTION_NAME := string;

WS := ' ';

CONTEXT_LIST := SOURCE_TERMINAL ',' NODE_LIST ',' TARGET_TERMINAL;

SOURCE_TERMINAL := TOKEN_LIST;
TARGET_TERMINAL := TOKEN_LIST;

NODE_LIST := NODE
          | NODE '|' NODE_LIST
          ;

NODE := string;
```

For the input code

```php
<?php
function f(int $x)
{
    return $x;
}
```

the following output will be produced


```
f long,parameter,x long,parameter|function|return|variable,x x,parameter,long x,parameter|function|return|variable,x x,variable|return|function|parameter,long x,variable|return|function|parameter,x
```

## Dependencies

To run `code2seq-paths` you'll need to have several dependencies installed on your system.

* [PHP 7+](https://php.net)
* [php-ast extension](https://github.com/nikic/php-ast)
* [Composer](https://get-composer.org)

These can be installed on Ubuntu machines via

```
sudo apt-get install php php-ast composer
```

Alternatively you can use the `.devcontainer/Dockerfile` to build
a system suitable for running `code2seq-paths`.

```
cd .devcontainer
docker compose build
```


## Development

To run tests you can run `./bin/test` which will execute all PHPUnit tests.
