# PHP Paths

Read PHP source code and produce a file suitable for use by [code2seq](http://code2seq.org)
for training and evaluating a model.

## Usage

To build the package just run `composer install`.

Once built you can run the tool via

```
./bin/paths path/to/php/dir > method_contexts.c2s
```

and from here you can use the file `method_contexts.c2s` as input to [code2seq](https://github.com/tech-srl/code2seq).
Keep in mind that the file produced here will need to be preprocessed within `code2seq`.

**TODO**: describe how to do that.

## Flags

You can see the available flags via `./bin/paths --help`.

```
Usage: ./bin/paths [options] [files...]]
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

**TODO**: Describe how to run from a docker container.

```
docker pull morria/php-paths
...
```

