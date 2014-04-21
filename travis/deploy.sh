#!/bin/bash

if [[ $TRAVIS_BRANCH == 'master' ]]
  gem install capistrano --no-ri --no-rdoc
  cap staging deploy
fi

