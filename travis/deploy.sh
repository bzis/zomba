#!/bin/bash
echo "TRAVIS_BRANCH: $TRAVIS_BRANCH"
echo "TRAVIS_PULL_REQUEST: $TRAVIS_PULL_REQUEST"
if [ "$TRAVIS_BRANCH" = 'master' ] && [ "$TRAVIS_PULL_REQUEST" = false ];
then
  GIT_USER=$(git log -1 "$TRAVIS_COMMIT" --pretty=format:"%an")
  GIT_EMAIL=$(git log -1 "$TRAVIS_COMMIT" --pretty=format:"%ae")
  git config user.name  "$GIT_USER"
  git config user.email "$GIT_EMAIL"
  gem install bundle
  bundle install
  bundle exec cap staging deploy
fi
