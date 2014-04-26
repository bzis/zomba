#!/usr/bin/php
<?php
var_dump($_SERVER['TRAVIS_PULL_REQUEST']);
if ($_SERVER['TRAVIS_BRANCH'] == 'master' && $_SERVER['TRAVIS_PULL_REQUEST'] == 'false') {
    $GIT_USER = `git log -1 \$TRAVIS_COMMIT --pretty=format:"%an"`;
    $GIT_EMAIL = `git log -1 \$TRAVIS_COMMIT --pretty=format:"%ae"`;
    echo `git config user.name "$GIT_USER" --global `;
    echo `git config user.name`;
    echo `git config user.email "$GIT_EMAIL"`;
    echo `gem install bundle`;
    echo `bundle install`;
    echo `bundle exec cap staging deploy`;
}