#!/bin/bash

if [[ $TRAVIS_BRANCH == 'master' ]]
  gem bundle install
  bundle install
  cap staging deploy
fi

