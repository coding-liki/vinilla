#!/bin/bash

type="php"
folder="/var/vinilla"
create_alias=false
# Разбираем аргументы командой строки
OPTIND=1
while getopts ":t:f:a" opt; do
    case "$opt" in
    t)
        echo "type will be $OPTARG" >&2
        type=$OPTARG
        ;;
    f)
        echo "folder will be $OPTARG" >&2
        folder=$OPTARG
        ;;
    a)
        echo "will create bash alias for vinilla" >&2
        create_alias=true
        ;;
    \?)
        echo "Invalid option: -$OPTARG" >&2
        ;;
    :)
        echo "Option -$OPTARG requires an argument." >&2
        exit 1
        ;;
    esac
done

# check for type existense
if [ -d "./$type" ]
then
    echo "can install $type now"
else
    echo "do not have that type"
    exit 1
fi

if [ -d "$folder" ]
then
    echo "can install in $folder now"
else
    mkdir -p $folder || exit 1
    echo "creating $folder now"
    echo "can install in $folder now"
fi



echo "installing '$type' type in '$folder'"

cp -r ./$type $folder/

echo "copy complete"

source ./bash/bash.sh


echo "complete installation"