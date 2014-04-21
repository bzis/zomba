#!/bin/bash

if [[ $TRAVIS_BRANCH == 'master' ]]
  cap staging deploy
fi

