#!/bin/bash

if [[ $TRAVIS_BRANCH == 'master' ]]
then
  gem bundle install
  bundle install
  cap staging deploy
fi

