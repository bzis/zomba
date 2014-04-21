#!/bin/bash

if [[ $TRAVIS_BRANCH == 'master' ]]
  bundle install
  cap staging deploy
fi

