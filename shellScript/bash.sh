
if ! grep ".vinillarc" ~/.bashrc
then
cp ./shellScript/bash.source ~/.vinillarc

interpretator=`cat ./$type/interpretator`
alias="alias vinilla_$type=\"$interpretator $folder/$type/vinilla`cat ./$type/extension`\""
echo "bash alias will be '$alias'"

echo $alias >> ~/.vinillarc

echo "
_vinilla_completions()
{
    COMPREPLY=()

    currentInputWord=\"\${COMP_WORDS[COMP_CWORD]}\"

    if [[ \${COMP_CWORD} == 1 ]] ; then
        availableCommands=\`vinilla_$type show-bins\`
        COMPREPLY=( \$(compgen -W \"\$availableCommands\" -- \$currentInputWord))
    fi

    commandName=\"\${COMP_WORDS[1]}\"

    case \$commandName in
    install)
        availablePackages=\`vinilla_$type list -c\`

        COMPREPLY=( \$(compgen -W \"\$availablePackages\" -- \$currentInputWord) )
        return 0
        ;;
    update|uninstall)
        installedPackages=\`vinilla_$type list -c -i\`

        COMPREPLY=( \$(compgen -W \"\$installedPackages\" -- \$currentInputWord) )
        return 0
        ;;
    esac

    return 0
}

" >>  ~/.vinillarc
echo "complete -F _vinilla_completions vinilla_$type" >> ~/.vinillarc

echo "source \$HOME/.vinillarc" >> ~/.bashrc
fi




