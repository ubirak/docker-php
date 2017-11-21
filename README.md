docker-php
==========

docker-php is a docker client writen in php. You can see it as a hack for some currently missing features in the official docker client.

## Born

The primary need was to find a workaround about docker stack deploy as at the time of the creation of this project it don't support some `--detach=false` option (see [docker/cli#373](https://github.com/docker/cli/issues/373)), so when it come to run end to end (e2e) tests on your `ci` (or your local machine) with some freshly deployed docker stack... you probably then launch your tests on a not ready stack!

## Commands

There's currently only one command:
```shell
docker run --rm -it -v /var/run/docker.sock:/var/run/docker.sock karibbu/docker-php:latest stack:converge <stack>
```

![stack converge demo](./demo/stack-converge.demo.gif)

For help and more options:
```shell
docker run --rm -it karibbu/docker-php:latest stack:converge --help
```

## Audience

docker-php is intended for people that want to hack around docker client for missing/not merged features.

Licensing
=========

docker-php is licensed under the Apache License, Version 2.0. See [LICENSE](https://github.com/karibbu/docker-php/blob/master/LICENSE) for the full license text.

