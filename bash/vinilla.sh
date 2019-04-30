#!/bin/bash
echo `pwd`
initial_pwd=$PWD
function getRepoToTemp(){
    local repo="$1"
    cd /tmp/
    
    if git clone $repo
    then
    echo "good"
    else
    echo "Не удалось скачать модуль $?"
    fi

}

repo_s=$( getRepoToTemp "/var/gitrepos/CodingLiki/Autoloader")

echo "repo is '$repo_s'"