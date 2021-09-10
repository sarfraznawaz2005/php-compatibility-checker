#!/bin/sh
git pull
git status
git add .
git commit -am "updated"
git push
echo Press Enter...
read