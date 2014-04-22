#!/bin/bash

if [ "${TRAVIS_BRANCH}" = 'master' ];
then
  gem install bundle
  bundle install
  bundle exec cap staging deploy
fi
