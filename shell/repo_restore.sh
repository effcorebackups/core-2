#!/usr/bin/env bash

cd ../

repo_origin=$(grep 'repo_origin: ' system/bundle.data | sed 's/  repo_origin: //g')
repo_branch=$(grep 'repo_branch: ' system/bundle.data | sed 's/  repo_branch: //g')

rm -rf .git
rm -rf dynamic/tmp/repo_system
git clone --branch=$repo_branch $repo_origin dynamic/tmp/repo_system
mv dynamic/tmp/repo_system/.git .
rm -rf dynamic/tmp/repo_system