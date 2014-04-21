#!/bin/bash

if [[ $TRAVIS_BRANCH == 'master' ]]
  gem bundle install
  bundle install
  bundle exec cap staging deploy
fi
