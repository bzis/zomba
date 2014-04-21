#!/bin/bash

if [[ $TRAVIS_BRANCH == 'master' ]]
then
  gem bundle install
  bundle install
  bundle exec cap staging deploy
fi
